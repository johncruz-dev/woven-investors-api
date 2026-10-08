<?php

namespace App\Http\Controllers\Education\Admin;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $this->learningPlatformService->refreshExpiredSubscriptions();

        $status = $request->string('status')->toString();

        $subscriptions = Subscription::query()
            ->with('user')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $users = User::query()
            ->where('is_active', true)
            ->whereIn('role', [UserRole::Student->value, UserRole::Teacher->value, UserRole::Counselor->value])
            ->orderBy('name')
            ->get();

        return view('education.admin.subscriptions', [
            'user' => $request->user(),
            'subscriptions' => $subscriptions,
            'users' => $users,
            'plans' => SubscriptionPlan::cases(),
            'statuses' => SubscriptionStatus::cases(),
            'statusFilter' => $status,
            'metrics' => $this->learningPlatformService->subscriptionMetrics(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'plan' => ['required', Rule::enum(SubscriptionPlan::class)],
            'billing_period' => ['required', 'in:monthly,yearly'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->learningPlatformService->createSubscription($validated);

        return back()->with('status', 'Subscription created.');
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $validated = $request->validate([
            'plan' => ['required', Rule::enum(SubscriptionPlan::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->learningPlatformService->updateSubscription($subscription, $validated);

        return back()->with('status', 'Subscription updated.');
    }

    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $this->learningPlatformService->cancelSubscription($subscription);

        return back()->with('status', 'Subscription canceled.');
    }

    public function extend(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($request->user()->canManagePaymentsAndSubscriptions(), 403);

        $validated = $request->validate([
            'billing_period' => ['required', 'in:monthly,yearly'],
        ]);

        $this->learningPlatformService->extendSubscription($subscription, $validated['billing_period']);

        return back()->with('status', 'Subscription extended.');
    }
}
