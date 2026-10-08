@extends('layouts.education')

@section('title', 'Subscriptions')

@section('content')
    <p class="muted"><a href="{{ route('education.admin.users.index') }}">← Admin</a></p>
    <div class="toolbar">
        <div>
            <h1>Payments & subscriptions</h1>
            <p class="subtitle">Manage plans, renewals, cancellations, and revenue</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('education.admin.reports.index') }}">Reports</a>
    </div>

    <div class="metrics">
        <div class="metric"><div class="label">Active</div><div class="value">{{ $metrics['active'] }}</div></div>
        <div class="metric"><div class="label">Past due</div><div class="value">{{ $metrics['past_due'] }}</div></div>
        <div class="metric"><div class="label">Expired</div><div class="value">{{ $metrics['expired'] }}</div></div>
        <div class="metric"><div class="label">Canceled</div><div class="value">{{ $metrics['canceled'] }}</div></div>
        <div class="metric"><div class="label">Active revenue</div><div class="value" style="font-size:1.2rem;">£{{ number_format($metrics['revenue'], 2) }}</div></div>
    </div>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Create subscription</h2>
        <form method="POST" action="{{ route('education.admin.subscriptions.store') }}" class="subscription-form">
            @csrf
            <div class="field">
                <label for="user_id">User</label>
                <select id="user_id" name="user_id" required>
                    @foreach ($users as $member)
                        <option value="{{ $member->id }}" @selected((string) old('user_id') === (string) $member->id)>
                            {{ $member->name }} ({{ $member->email }}) · {{ $member->role->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="plan">Plan</label>
                    <select id="plan" name="plan" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->value }}" @selected(old('plan', 'standard') === $plan->value)>
                                {{ $plan->label() }} — £{{ number_format($plan->monthlyAmount(), 2) }}/mo
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="billing_period">Billing period</label>
                    <select id="billing_period" name="billing_period" required>
                        <option value="monthly" @selected(old('billing_period') === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(old('billing_period', 'yearly') === 'yearly')>Yearly (2 months free)</option>
                    </select>
                </div>
                <div class="field">
                    <label for="amount">Amount (optional override)</label>
                    <input id="amount" type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" placeholder="Auto from plan">
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', 'active') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="starts_at">Starts at</label>
                    <input id="starts_at" type="date" name="starts_at" value="{{ old('starts_at', now()->toDateString()) }}">
                </div>
                <div class="field">
                    <label for="ends_at">Ends at (optional)</label>
                    <input id="ends_at" type="date" name="ends_at" value="{{ old('ends_at') }}">
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Create subscription</button>
        </form>
    </div>

    <div class="card">
        <form method="GET" class="toolbar" style="margin-bottom:1rem;">
            <div class="field" style="margin-bottom:0;min-width:180px;">
                <label for="status_filter">Filter by status</label>
                <select id="status_filter" name="status" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($statusFilter === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        @forelse ($subscriptions as $subscription)
            <div class="subscription-row">
                <div class="subscription-meta">
                    <strong>{{ $subscription->user->name }}</strong>
                    <span class="muted">{{ $subscription->user->email }}</span>
                    <div class="inline-actions" style="margin-top:0.35rem;">
                        <span class="status status-{{ $subscription->status->value }}">{{ $subscription->status->label() }}</span>
                        <span class="muted">{{ $subscription->planLabel() }}</span>
                        <span class="muted">£{{ number_format((float) $subscription->amount, 2) }} {{ $subscription->currency }}</span>
                        <span class="muted">
                            {{ $subscription->starts_at?->format('d M Y') ?? '—' }}
                            →
                            {{ $subscription->ends_at?->format('d M Y') ?? 'open' }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('education.admin.subscriptions.update', $subscription) }}" class="subscription-edit">
                    @csrf
                    @method('PUT')
                    <select name="plan" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->value }}" @selected($subscription->plan === $plan->value)>{{ $plan->label() }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ $subscription->amount }}" required>
                    <select name="status" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($subscription->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="starts_at" value="{{ $subscription->starts_at?->toDateString() }}">
                    <input type="date" name="ends_at" value="{{ $subscription->ends_at?->toDateString() }}">
                    <button class="btn btn-secondary" type="submit">Save</button>
                </form>

                <div class="inline-actions">
                    <form method="POST" action="{{ route('education.admin.subscriptions.extend', $subscription) }}">
                        @csrf
                        <input type="hidden" name="billing_period" value="yearly">
                        <button class="btn btn-ghost" type="submit">Extend 1y</button>
                    </form>
                    <form method="POST" action="{{ route('education.admin.subscriptions.extend', $subscription) }}">
                        @csrf
                        <input type="hidden" name="billing_period" value="monthly">
                        <button class="btn btn-ghost" type="submit">Extend 1m</button>
                    </form>
                    @if ($subscription->status !== \App\Enums\SubscriptionStatus::Canceled)
                        <form method="POST" action="{{ route('education.admin.subscriptions.cancel', $subscription) }}" onsubmit="return confirm('Cancel this subscription?');">
                            @csrf
                            <button class="btn btn-ghost danger" type="submit">Cancel</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="empty">No subscriptions match this filter.</p>
        @endforelse

        <div class="pagination">{{ $subscriptions->links('pagination::simple-default') }}</div>
    </div>
@endsection
