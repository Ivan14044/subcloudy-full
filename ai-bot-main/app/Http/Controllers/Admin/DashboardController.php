<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'today');
        $startDate = null;
        $endDate = Carbon::now()->endOfDay(); // по умолчанию до конца сегодняшнего дня

        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek()->startOfDay();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                break;
            case 'all':
                $startDate = null;
                break;
            case 'custom':
                if ($request->filled('start_date')) {
                    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                }
                if ($request->filled('end_date')) {
                    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                }
                break;
        }

        $dateFilter = function ($query) use ($startDate, $endDate) {
            if ($startDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        };

        $totalUsers = User::where('is_admin', false)->where('is_pending', 0)->where($dateFilter)->count();

        $activeSubscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('is_trial', 0)
            ->where($dateFilter)
            ->count();

        $activeTrialSubscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('is_trial', 1)
            ->where($dateFilter)
            ->count();

        $totalAmount = Transaction::where($dateFilter)->sum('amount');
        $totalTransactions = Transaction::where($dateFilter)->count();

        // Calculate trial to paid conversion
        $conversionData = $this->calculateTrialConversion($startDate, $endDate);

        // Get chart data
        $revenueData = $this->getRevenueData($startDate, $endDate);
        $usersData = $this->getUsersData($startDate, $endDate);
        $subscriptionsData = $this->getSubscriptionsData($startDate, $endDate);
        $conversionChartData = $this->getConversionData($startDate, $endDate);

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeSubscriptions',
            'activeTrialSubscriptions',
            'totalAmount',
            'totalTransactions',
            'period',
            'startDate',
            'endDate',
            'conversionData',
            'revenueData',
            'usersData',
            'subscriptionsData',
            'conversionChartData'
        ));
    }

    private function calculateTrialConversion($startDate, $endDate)
    {
        // Find all users who had trial subscriptions in the period
        $query = Subscription::where('is_trial', 1);
        
        if ($startDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->where('created_at', '<=', $endDate);
        }
        
        $trialSubscriptions = $query->get();

        $trialUserIds = $trialSubscriptions->pluck('user_id')->unique();
        $trialServiceIds = $trialSubscriptions->pluck('service_id')->unique();

        $trialCount = $trialSubscriptions->count();

        // Find users who converted to paid subscriptions for the same services
        $convertedCount = 0;
        foreach ($trialUserIds as $userId) {
            $userTrialServices = $trialSubscriptions->where('user_id', $userId)->pluck('service_id');
            
            foreach ($userTrialServices as $serviceId) {
                // Check if user has a paid subscription for this service after the trial
                $trialSub = $trialSubscriptions->where('user_id', $userId)
                    ->where('service_id', $serviceId)
                    ->first();
                
                if ($trialSub) {
                    $paidSub = Subscription::where('user_id', $userId)
                        ->where('service_id', $serviceId)
                        ->where('is_trial', 0)
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->where('created_at', '>', $trialSub->created_at)
                        ->first();
                    
                    if ($paidSub) {
                        $convertedCount++;
                        break; // Count each user only once
                    }
                }
            }
        }

        $conversionRate = $trialCount > 0 ? ($convertedCount / $trialCount) * 100 : 0;

        return [
            'trial_count' => $trialCount,
            'converted_count' => $convertedCount,
            'conversion_rate' => round($conversionRate, 2),
        ];
    }

    private function getRevenueData($startDate, $endDate)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
        }

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $transactions->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray(),
            'data' => $transactions->pluck('total')->toArray(),
        ];
    }

    private function getUsersData($startDate, $endDate)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
        }

        $users = User::where('is_admin', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $users->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray(),
            'data' => $users->pluck('total')->toArray(),
        ];
    }

    private function getSubscriptionsData($startDate, $endDate)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
        }

        $activeQuery = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereBetween('created_at', [$startDate, $endDate]);
        $active = $activeQuery->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $canceledQuery = Subscription::where('status', Subscription::STATUS_CANCELED)
            ->whereBetween('created_at', [$startDate, $endDate]);
        $canceled = $canceledQuery->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $allDates = $active->pluck('date')->merge($canceled->pluck('date'))->unique()->sort()->values();

        return [
            'labels' => $allDates->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray(),
            'active' => $this->fillMissingDates($allDates, $active),
            'canceled' => $this->fillMissingDates($allDates, $canceled),
        ];
    }

    private function getConversionData($startDate, $endDate)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
        }

        $conversions = [];
        $dates = collect();

        // Group by date
        $query = Subscription::where('is_trial', 1);
        if ($startDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->where('created_at', '<=', $endDate);
        }
        
        $trialSubs = $query->get()
            ->groupBy(function ($sub) {
                return Carbon::parse($sub->created_at)->format('Y-m-d');
            });

        foreach ($trialSubs as $date => $subs) {
            $dates->push($date);
            $trialCount = $subs->count();
            $convertedCount = 0;

            foreach ($subs as $trialSub) {
                $paidSub = Subscription::where('user_id', $trialSub->user_id)
                    ->where('service_id', $trialSub->service_id)
                    ->where('is_trial', 0)
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->where('created_at', '>', $trialSub->created_at)
                    ->first();

                if ($paidSub) {
                    $convertedCount++;
                }
            }

            $rate = $trialCount > 0 ? ($convertedCount / $trialCount) * 100 : 0;
            $conversions[] = round($rate, 2);
        }

        return [
            'labels' => $dates->sort()->values()->toArray(),
            'data' => $conversions,
        ];
    }

    private function fillMissingDates($allDates, $data)
    {
        $result = [];
        $dataMap = $data->keyBy('date');

        foreach ($allDates as $date) {
            $result[] = $dataMap->get($date)->total ?? 0;
        }

        return $result;
    }
}
