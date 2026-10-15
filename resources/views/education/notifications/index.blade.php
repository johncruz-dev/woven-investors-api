@extends('layouts.education')

@section('title', 'Notifications')

@section('content')
    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
            <p class="eyebrow">Alerts</p>
            <h1>Notifications</h1>
            <p class="subtitle">Lesson schedules and upcoming video calls</p>
        </div>
        @if ($notifications->whereNull('read_at')->isNotEmpty())
            <form method="POST" action="{{ route('education.notifications.read-all') }}">
                @csrf
                <button class="btn btn-secondary" type="submit">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="card">
        @forelse ($notifications as $notification)
            <a href="{{ route('education.notifications.read', $notification) }}" class="notification-row {{ $notification->isUnread() ? 'is-unread' : '' }}">
                <div class="notification-row-main">
                    <div class="message-row-title">
                        <strong>{{ $notification->title }}</strong>
                        @if ($notification->isUnread())
                            <span class="status status-submitted">New</span>
                        @endif
                    </div>
                    <div class="muted">{{ $notification->body }}</div>
                    <div class="muted">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
            </a>
        @empty
            <p class="empty">No notifications yet.</p>
        @endforelse
    </div>
@endsection
