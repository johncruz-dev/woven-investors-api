@extends('layouts.education')

@section('title', 'Messages')

@section('content')
    <div class="toolbar">
        <div>
            <h1>Messages</h1>
            <p class="subtitle">Send and receive messages with other platform members</p>
        </div>
        @if ($unreadCount > 0)
            <span class="status status-submitted">{{ $unreadCount }} unread</span>
        @endif
    </div>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">New message</h2>
        @if ($recipients->isEmpty())
            <p class="empty">No recipients available.</p>
        @else
            <form method="POST" action="{{ route('education.messages.store') }}">
                @csrf
                <div class="field">
                    <label for="recipient_id">To</label>
                    <select id="recipient_id" name="recipient_id" required>
                        @foreach ($recipients as $recipient)
                            <option value="{{ $recipient->id }}" @selected((string) old('recipient_id') === (string) $recipient->id)>
                                {{ $recipient->name }} ({{ $recipient->role->label() }})
                            </option>
                        @endforeach
                    </select>
                    @error('recipient_id')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="subject">Subject</label>
                    <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required>
                </div>
                <div class="field">
                    <label for="body">Message</label>
                    <textarea id="body" name="body" rows="4" required>{{ old('body') }}</textarea>
                </div>
                <button class="btn btn-primary" type="submit">Send</button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="toolbar" style="margin-bottom:1rem;">
            <div class="inline-actions">
                <a class="btn {{ $tab === 'inbox' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('education.messages.index', ['tab' => 'inbox']) }}">
                    Inbox
                    @if ($unreadCount > 0)
                        <span class="nav-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                </a>
                <a class="btn {{ $tab === 'sent' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('education.messages.index', ['tab' => 'sent']) }}">Sent</a>
            </div>
        </div>

        @if ($tab === 'sent')
            @forelse ($sent as $message)
                <div class="message-row">
                    <div class="message-row-main">
                        <a href="{{ route('education.messages.show', $message) }}"><strong>{{ $message->subject }}</strong></a>
                        <div class="muted">
                            To {{ $message->recipient->name }}
                            · {{ $message->created_at->diffForHumans() }}
                            · {{ $message->read_at ? 'Read' : 'Delivered' }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="empty">No sent messages yet.</p>
            @endforelse
            <div class="pagination">{{ $sent->links('pagination::simple-default') }}</div>
        @else
            @forelse ($inbox as $message)
                <a href="{{ route('education.messages.show', $message) }}" class="message-row {{ $message->read_at ? '' : 'is-unread' }}">
                    <div class="message-row-main">
                        <div class="message-row-title">
                            <strong>{{ $message->subject }}</strong>
                            @unless ($message->read_at)
                                <span class="status status-submitted">New</span>
                            @endunless
                        </div>
                        <div class="muted">
                            From {{ $message->sender->name }}
                            · {{ $message->created_at->diffForHumans() }}
                        </div>
                        <div class="message-row-preview">{{ \Illuminate\Support\Str::limit($message->body, 100) }}</div>
                    </div>
                </a>
            @empty
                <p class="empty">Inbox empty.</p>
            @endforelse
            <div class="pagination">{{ $inbox->links('pagination::simple-default') }}</div>
        @endif
    </div>
@endsection
