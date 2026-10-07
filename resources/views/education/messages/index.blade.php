@extends('layouts.education')

@section('title', 'Messages')

@section('content')
    <h1>Messages</h1>
    <p class="subtitle">Communicate with {{ $user->isStudent() ? 'teachers' : 'students' }}</p>

    <div class="card" style="max-width:720px;">
        <h2 style="font-size:1rem;margin-bottom:1rem;">New message</h2>
        <form method="POST" action="{{ route('education.messages.store') }}">
            @csrf
            <div class="field">
                <label>To</label>
                <select name="recipient_id" required>
                    @foreach ($recipients as $recipient)
                        <option value="{{ $recipient->id }}">{{ $recipient->name }} ({{ $recipient->role->label() }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label>Subject</label><input type="text" name="subject" required></div>
            <div class="field"><label>Message</label><textarea name="body" rows="4" required></textarea></div>
            <button class="btn btn-primary" type="submit">Send</button>
        </form>
    </div>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Inbox</h2>
        @forelse ($inbox as $message)
            <div style="padding:0.5rem 0;border-bottom:1px solid var(--border);">
                <a href="{{ route('education.messages.show', $message) }}"><strong>{{ $message->subject }}</strong></a>
                <div class="muted">From {{ $message->sender->name }} · {{ $message->created_at->diffForHumans() }} @unless($message->read_at)<strong>· unread</strong>@endunless</div>
            </div>
        @empty
            <p class="empty">Inbox empty.</p>
        @endforelse
    </div>
@endsection
