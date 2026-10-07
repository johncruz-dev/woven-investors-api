@extends('layouts.education')

@section('title', 'Reports')

@section('content')
    <p class="muted"><a href="{{ route('education.admin.users.index') }}">← Admin</a></p>
    <h1>Platform reports</h1>
    <div class="metrics">
        @foreach ($report as $label => $value)
            <div class="metric">
                <div class="label">{{ str_replace('_', ' ', $label) }}</div>
                <div class="value">{{ $value ?? 0 }}</div>
            </div>
        @endforeach
    </div>
@endsection
