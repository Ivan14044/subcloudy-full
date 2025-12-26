<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\UserLogin;
use App\Models\SubscriptionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $services = Service::all();
        return view('admin.users.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'is_blocked' => 'required|boolean',
            'password' => 'required|min:6|confirmed',
            'days' => 'required|integer|min:1',
            'services' => 'required|array|min:1',
            'services.*' => 'integer|exists:services,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'is_blocked' => $request->is_blocked,
            'password' => Hash::make($request->password),
            'is_pending' => 1,
            'sub_data' => [
                'days' => $validated['days'],
                'services' => $validated['services'],
            ],
        ]);

        return redirect()->route('admin.users.index')->with('success', __('admin.user.created'));
    }

    public function edit(User $user)
    {
        $subscriptions = $user->subscriptions()
            ->orderByDesc('created_at')
            ->get();

        $loginHistory = UserLogin::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $subscriptionLogs = SubscriptionLog::where('user_id', $user->id)
            ->with('subscription.service')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.users.edit', compact('user', 'subscriptions', 'loginHistory', 'subscriptionLogs'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_blocked' => 'required',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $is_blocked = $request->is_blocked;
        $is_pending = $user->is_pending;
        if ($request->is_blocked == 2) {
            $is_blocked = 0;
            $is_pending = 1;
        } elseif ($request->is_blocked == 0) {
            $is_blocked = 0;
            $is_pending = 0;
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_blocked' => $is_blocked,
            'is_pending' => $is_pending,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        $route = $request->has('save')
            ? route('admin.users.edit', $user->id)
            : route('admin.users.index');

        return redirect($route)->with('success', __('admin.user.updated'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('admin.user.deleted'));
    }

    public function block(User $user)
    {
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', $user->is_blocked ? 'User has been blocked.' : 'User has been unblocked.');
    }

    public function bulkInsert()
    {
        $services = Service::all();

        return view('admin.users.bulk-insert', compact('services'));
    }

    public function bulkInsertStore(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:50',
            'number' => 'required|integer|min:1|max:1000',
            'days'   => 'required|integer|min:1',
            'services' => 'required|array|min:1',
            'services.*' => 'integer|exists:services,id',
        ]);

        $prefix = $validated['prefix'];
        $number = $validated['number'];

        $users = [];
        $logins = [];

        for ($i = 0; $i < $number; $i++) {
            do {
                $login = $prefix . Str::upper(Str::random(6));
            } while (User::where('name', $login)->exists());

            $email = $login . '@example.com';
            $passwordPlain = Str::random(12);

            $users[] = [
                'name' => $login,
                'email' => $email,
                'password' => Hash::make($passwordPlain),
                'is_pending' => 1,
                'sub_data' => json_encode([
                    'days' => $validated['days'],
                    'services' => $validated['services']
                ]),
                'created_at' => now()
            ];

            $logins[] = "$email:$passwordPlain";
        }

        User::insert($users);

        $filename = 'bulk_users.txt';
        Storage::disk('local')->put($filename, implode(PHP_EOL, $logins));

        return redirect()
            ->route('admin.users.index')
            ->with([
                'success' => "$number users created successfully.",
                'download_bulk_file' => true
            ]);
    }

    public function downloadBulkUsers()
    {
        $filename = 'bulk_users.txt';

        if (!Storage::disk('local')->exists($filename)) {
            abort(404, 'File not found');
        }

        return response()->download(storage_path('app/' . $filename));
    }

    public function subscriptions(User $user)
    {
        $subscriptions = $user->subscriptions()
            ->orderByDesc('created_at')
            ->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'user'));
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'status' => 'required|in:active,blocked,pending',
        ]);

        $userIds = $validated['user_ids'];
        $status = $validated['status'];

        DB::transaction(function () use ($userIds, $status) {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user && !$user->is_admin) {
                    switch ($status) {
                        case 'active':
                            $user->is_blocked = 0;
                            $user->is_pending = 0;
                            break;
                        case 'blocked':
                            $user->is_blocked = 1;
                            $user->is_pending = 0;
                            break;
                        case 'pending':
                            $user->is_blocked = 0;
                            $user->is_pending = 1;
                            break;
                    }
                    $user->save();
                }
            }
        });

        $count = count($userIds);
        return redirect()->route('admin.users.index')
            ->with('success', __('admin.bulk_operation_success', ['count' => $count]));
    }

    public function bulkAddFreeDays(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'days' => 'required|integer|min:1|max:365',
            'service_id' => 'nullable|integer|exists:services,id',
        ]);

        $userIds = $validated['user_ids'];
        $days = $validated['days'];
        $serviceId = $validated['service_id'] ?? null;

        DB::transaction(function () use ($userIds, $days, $serviceId) {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user && !$user->is_admin) {
                    $query = Subscription::where('user_id', $userId)
                        ->where('status', Subscription::STATUS_ACTIVE);

                    if ($serviceId) {
                        $query->where('service_id', $serviceId);
                    }

                    $subscriptions = $query->get();

                    foreach ($subscriptions as $subscription) {
                        $currentDate = $subscription->next_payment_at 
                            ? Carbon::parse($subscription->next_payment_at)
                            : Carbon::now();
                        
                        if ($currentDate->lt(Carbon::now())) {
                            $currentDate = Carbon::now();
                        }

                        $subscription->next_payment_at = $currentDate->addDays($days);
                        $subscription->save();
                    }
                }
            }
        });

        $count = count($userIds);
        return redirect()->route('admin.users.index')
            ->with('success', __('admin.bulk_operation_success', ['count' => $count]));
    }
}
