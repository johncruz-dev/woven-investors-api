@extends('layouts.education')

@section('title', $ticket->subject)

@section('content')
    <p class="muted"><a href="{{ route('education.tickets.index') }}">← Tickets</a></p>
    <h1>{{ $ticket->subject }}</h1>
    <p class="subtitle">
        {{ $ticket->student->name }} ·
        <span class="status status-{{ $ticket->status->value }}">{{ $ticket->status->label() }}</span> ·
        {{ $ticket->priority->label() }} priority
    </p>

    <div class="card">
        <p class="muted">Opened by {{ $ticket->creator->name }} on {{ $ticket->created_at->format('d M Y H:i') }}</p>
        <p style="margin-top:1rem;white-space:pre-wrap;">{{ $ticket->body }}</p>
        <p class="muted" style="margin-top:1rem;">Assignee: {{ $ticket->assignee?->name ?? 'Unassigned' }}</p>
    </div>

    @if ($user->canManageTickets())
        <div class="card" style="max-width:520px;">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Update ticket</h2>
            <form method="POST" action="{{ route('education.tickets.update', $ticket) }}">
                @csrf
                @method('PUT')

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($ticket->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="assigned_to">Assignee</label>
                    <select id="assigned_to" name="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected($ticket->assigned_to === $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn btn-primary" type="submit">Save changes</button>
            </form>
        </div>
    @endif
@endsection
