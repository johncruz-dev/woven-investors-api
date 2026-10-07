@extends('layouts.education')

@section('title', 'Security')

@section('content')
    <p class="muted"><a href="{{ route('education.admin.users.index') }}">← Admin</a></p>
    <h1>Maintain security</h1>

    <div class="metrics">
        <div class="metric"><div class="label">Auth required</div><div class="value" style="font-size:1rem;">{{ $authRequired ? 'On' : 'Off' }}</div></div>
        <div class="metric"><div class="label">Registration</div><div class="value" style="font-size:1rem;">{{ $registrationEnabled ? 'Open' : 'Closed' }}</div></div>
        <div class="metric"><div class="label">Security headers</div><div class="value" style="font-size:1rem;">{{ $headersEnabled ? 'On' : 'Off' }}</div></div>
        <div class="metric"><div class="label">API tokens</div><div class="value">{{ $activeTokens }}</div></div>
        <div class="metric"><div class="label">Inactive users</div><div class="value">{{ $inactiveUsers }}</div></div>
    </div>

    <div class="card">
        <p class="muted">Use user management to deactivate accounts. From here you can revoke API tokens for security incidents.</p>
        <form method="POST" action="{{ route('education.admin.security.revoke-tokens', $user) }}" style="margin-top:1rem;display:inline-block;">
            @csrf
            <button class="btn btn-secondary" type="submit">Revoke my API tokens</button>
        </form>
    </div>
@endsection
