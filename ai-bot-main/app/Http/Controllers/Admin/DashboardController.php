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

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeSubscriptions',
            'activeTrialSubscriptions',
            'totalAmount',
            'totalTransactions',
            'period',
            'startDate',
            'endDate'
        ));
    }
}
