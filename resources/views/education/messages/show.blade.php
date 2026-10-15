@extends('layouts.education')

@section('title', $message->subject)

@section('content')
    <p class="muted"><a href="{{ route('education.messages.index') }}">← Messages</a></p>

    <div class="toolbar">
        <div>
            <h1>{{ $message->subject }}</h1>
            <p class="subtitle">
                From {{ $message->sender->name }} to {{ $message->recipient->name }}
                · {{ $message->created_at->format('d M Y H:i') }}
            </p>
        </div>
        @if ($message->recipient_id === $user->id)
            <span class="status status-graded">Read</span>
        @elseif ($message->read_at)
            <span class="status status-graded">Read by recipient</span>
        @else
            <span class="status status-submitted">Delivered</span>
        @endif
    </div>

    <div class="card message-body">{{ $message->body }}</div>

    @if ($message->sender_id !== $user->id)
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Reply</h2>
            <form method="POST" action="{{ route('education.messages.store') }}">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $message->sender_id }}">
                <div class="field">
                    <label for="subject">Subject</label>
                    <input id="subject" type="text" name="subject" value="Re: {{ $message->subject }}" required>
                </div>
                <div class="field">
                    <label for="body">Message</label>
                    <textarea id="body" name="body" rows="4" required></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Send reply</button>
            </form>
        </div>
    @endif
@endsection
