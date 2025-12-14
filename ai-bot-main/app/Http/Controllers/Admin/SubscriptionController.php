<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Service;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::orderBy('id', 'desc')->get();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $services = Service::all();
        $users = User::where('is_admin', 0)->where('is_main_admin', 0)->get();

        return view('admin.subscriptions.create', compact('services', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required'],
            'next_payment_at' => ['required', 'date', 'after:now'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $validated['payment_method'] = 'default';
        Subscription::create($validated);


        $redirectUrl = $request->input('return_url')
            ? redirect($request->input('return_url'))
            : redirect()->route('admin.subscriptions.index');

        return $redirectUrl->with('success', __('admin.subscription.created'));
    }

    public function edit(Subscription $subscription)
    {
        $services = Service::all();

        return view('admin.subscriptions.edit', compact('subscription', 'services'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'status' => ['required'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);

        $subscription->update($validated);

        $redirectUrl = $request->input('return_url')
            ? redirect($request->input('return_url'))
            : redirect()->route('admin.subscriptions.index');

        return $redirectUrl->with('success', __('admin.subscription.updated'));
    }

    public function destroy(Request $request, Subscription $subscription)
    {
        $subscription->delete();

        $redirectUrl = $request->input('return_url')
            ? redirect($request->input('return_url'))
            : redirect()->route('admin.subscriptions.index');

        return $redirectUrl->with('success', __('admin.subscription.deleted'));
    }

    public function transactions(Request $request, Subscription $subscription)
    {
        $query = $subscription->transactions();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $transactions = $query->orderByDesc('created_at')->get();
        $user = $subscription->user;

        return view('admin.subscriptions.transactions', compact('transactions', 'subscription', 'user'));
    }

    public function updateTransactionStatus(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,failed,refunded'],
        ]);

        $transaction->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', __('admin.transaction_status.updated'));
    }

    public function updateNextPayment(Request $request, Subscription $subscription)
    {
        $request->validate([
            'next_payment_at' => 'required|date',
        ]);

        $subscription->next_payment_at = $request->input('next_payment_at');
        $subscription->save();

        return redirect()->back()->with('success', 'Next payment date updated.');
    }

    public function toggleStatus(Request $request, Subscription $subscription)
    {
        $subscription->status = $subscription->status == Subscription::STATUS_ACTIVE ? Subscription::STATUS_CANCELED : Subscription::STATUS_ACTIVE;
        $subscription->save();

        return redirect()->back()->with('success', 'Subscription status updated successfully.');
    }

    public function extendForm()
    {
        $services = Service::all();

        return view('admin.subscriptions.extend', compact('services'));
    }

    public function extendMany(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'hours' => ['required', 'integer', 'min:1'],
        ]);

        $count = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('service_id', $validated['service_id'])
            ->update([
                'next_payment_at' => \DB::raw("DATE_ADD(next_payment_at, INTERVAL {$validated['hours']} HOUR)")
            ]);

        return redirect()->route('admin.subscriptions.index')->with('success', "$count subscriptions extended by {$validated['hours']} hours.");
    }
}
