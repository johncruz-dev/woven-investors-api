@extends('layouts.education')

@section('title', $student->name)

@section('content')
    <p class="muted"><a href="{{ route('education.students.index') }}">← Students</a></p>
    <h1>{{ $student->name }}</h1>
    <p class="subtitle">{{ $student->external_id }} · Grade {{ $student->grade_level ?? '—' }} · {{ $student->email ?? 'No email' }}</p>

    <div class="toolbar">
        <h2 style="font-size:1.1rem;">Support history</h2>
        <a class="btn btn-primary" href="{{ route('education.tickets.create', ['student_id' => $student->id]) }}">New ticket</a>
    </div>

    <div class="card">
        @if ($student->supportTickets->count())
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($student->supportTickets as $ticket)
                        <tr>
                            <td><a href="{{ route('education.tickets.show', $ticket) }}">{{ $ticket->subject }}</a></td>
                            <td><span class="status status-{{ $ticket->status->value }}">{{ $ticket->status->label() }}</span></td>
                            <td>{{ $ticket->priority->label() }}</td>
                            <td>{{ $ticket->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No support tickets for this student yet.</p>
        @endif
    </div>
@endsection
