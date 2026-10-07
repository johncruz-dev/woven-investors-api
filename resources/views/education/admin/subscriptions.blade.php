@extends('layouts.education')

@section('title', 'Subscriptions')

@section('content')
    <p class="muted"><a href="{{ route('education.admin.users.index') }}">← Admin</a></p>
    <h1>Payments & subscriptions</h1>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Create subscription</h2>
        <form method="POST" action="{{ route('education.admin.subscriptions.store') }}">
            @csrf
            <div class="field">
                <label>User</label>
                <select name="user_id" required>
                    @foreach ($users as $member)
                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label>Plan</label><input type="text" name="plan" value="standard" required></div>
            <div class="field"><label>Amount</label><input type="number" step="0.01" name="amount" value="29.00" required></div>
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="canceled">Canceled</option>
                    <option value="past_due">Past due</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Create</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>User</th><th>Plan</th><th>Amount</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->user->name }}</td>
                        <td>{{ $subscription->plan }}</td>
                        <td>{{ $subscription->currency }} {{ $subscription->amount }}</td>
                        <td>{{ $subscription->status }}</td>
                        <td>
                            <form method="POST" action="{{ route('education.admin.subscriptions.update', $subscription) }}">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()">
                                    @foreach (['active','canceled','past_due'] as $status)
                                        <option value="{{ $status }}" @selected($subscription->status === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $subscriptions->links('pagination::simple-default') }}</div>
    </div>
@endsection
