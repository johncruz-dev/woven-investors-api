<?php

namespace App\Http\Controllers\Education\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $subscriptions = Subscription::query()->with('user')->latest()->paginate(20);
        $users = User::query()->orderBy('name')->get();

        return view('education.admin.subscriptions', [
            'user' => $request->user(),
            'subscriptions' => $subscriptions,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'plan' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,canceled,past_due'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        Subscription::create([
            ...$validated,
            'currency' => 'GBP',
            'starts_at' => $validated['starts_at'] ?? now(),
        ]);

        return back()->with('status', 'Subscription created.');
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,canceled,past_due'],
        ]);

        $subscription->update($validated);

        return back()->with('status', 'Subscription updated.');
    }
}
