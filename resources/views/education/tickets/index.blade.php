@extends('layouts.education')

@section('title', 'Tickets')

@section('content')
    <div class="toolbar">
        <div>
            <h1>Support tickets</h1>
            <p class="subtitle">{{ $school->name }}</p>
        </div>
        <a class="btn btn-primary" href="{{ route('education.tickets.create') }}">New ticket</a>
    </div>

    <div class="card">
        <form method="GET" class="toolbar">
            <div style="min-width:180px;">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $item)
                        <option value="{{ $item->value }}" @selected($status === $item->value)>{{ $item->label() }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        @if ($tickets->count())
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Student</th>
                        <th>Assignee</th>
                        <th>Status</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td><a href="{{ route('education.tickets.show', $ticket) }}">{{ $ticket->subject }}</a></td>
                            <td>{{ $ticket->student->name }}</td>
                            <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                            <td><span class="status status-{{ $ticket->status->value }}">{{ $ticket->status->label() }}</span></td>
                            <td class="{{ $ticket->priority === \App\Enums\TicketPriority::High ? 'priority-high' : '' }}">{{ $ticket->priority->label() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">{{ $tickets->links('pagination::simple-default') }}</div>
        @else
            <p class="empty">No tickets match this filter.</p>
        @endif
    </div>
@endsection
