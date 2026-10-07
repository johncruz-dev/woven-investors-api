@extends('layouts.education')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1>
            @if ($user->isStudent())
                My learning
            @else
                Education support
            @endif
        </h1>
        <p class="subtitle">
            @if ($school)
                {{ $school->name }} — courses, support tickets, and student success
            @else
                No school configured yet. Run <code>php artisan db:seed</code>.
            @endif
        </p>
    </div>

    @if ($user->isStudent())
        @if (count($learning))
            <div class="metrics">
                <div class="metric">
                    <div class="label">Enrolled courses</div>
                    <div class="value">{{ count($learning) }}</div>
                </div>
                <div class="metric">
                    <div class="label">Lessons completed</div>
                    <div class="value">{{ collect($learning)->sum(fn ($row) => $row['progress']['lessons_completed']) }}</div>
                </div>
                <div class="metric">
                    <div class="label">Assignments submitted</div>
                    <div class="value">{{ collect($learning)->sum(fn ($row) => $row['progress']['assignments_submitted']) }}</div>
                </div>
            </div>

            <div class="card">
                <h2 style="font-size:1rem;margin-bottom:1rem;">Course progress</h2>
                @foreach ($learning as $row)
                    <div class="learning-row">
                        <div class="learning-row-main">
                            <a href="{{ route('education.courses.show', $row['course']) }}"><strong>{{ $row['course']->title }}</strong></a>
                            <div class="muted">{{ $row['course']->teacher->name ?? 'Instructor' }}</div>
                            <div class="progress-track compact" aria-hidden="true">
                                <div class="progress-fill" style="width: {{ min(100, $row['progress']['lesson_percent']) }}%;"></div>
                            </div>
                        </div>
                        <div class="learning-row-stats">
                            <span>{{ $row['progress']['lesson_percent'] }}% lessons</span>
                            <span>{{ $row['progress']['assignments_submitted'] }}/{{ $row['progress']['assignments_total'] }} assignments</span>
                            <span>Avg {{ $row['progress']['average_score'] !== null ? round($row['progress']['average_score'], 1) : '—' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <p class="empty">You are not enrolled in any courses yet.</p>
                <a class="btn btn-primary" href="{{ route('education.courses.index') }}">Browse courses</a>
            </div>
        @endif
    @else
        <div class="metrics">
            <div class="metric">
                <div class="label">Students</div>
                <div class="value">{{ $metrics['students'] }}</div>
            </div>
            <div class="metric">
                <div class="label">Open tickets</div>
                <div class="value">{{ $metrics['open_tickets'] }}</div>
            </div>
            <div class="metric">
                <div class="label">Resolved</div>
                <div class="value">{{ $metrics['resolved_tickets'] }}</div>
            </div>
            <div class="metric">
                <div class="label">High priority open</div>
                <div class="value">{{ $metrics['high_priority_open'] }}</div>
            </div>
        </div>
    @endif

    <div class="toolbar">
        <h2 style="font-size:1.1rem;">Quick links</h2>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a class="btn btn-primary" href="{{ route('education.courses.index') }}">Courses</a>
            <a class="btn btn-secondary" href="{{ route('education.tickets.create') }}">New support ticket</a>
            @if ($user->canManageUsersAndPermissions())
                <a class="btn btn-secondary" href="{{ route('education.admin.users.index') }}">Admin panel</a>
            @endif
        </div>
    </div>

    <div class="toolbar">
        <h2 style="font-size:1.1rem;">Recent support tickets</h2>
        <div style="display:flex;gap:0.5rem;">
            <a class="btn btn-secondary" href="{{ route('education.tickets.index') }}">View all</a>
        </div>
    </div>

    <div class="card">
        @if ($recentTickets && $recentTickets->count())
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentTickets as $ticket)
                        <tr>
                            <td><a href="{{ route('education.tickets.show', $ticket) }}">{{ $ticket->subject }}</a></td>
                            <td>{{ $ticket->student->name }}</td>
                            <td><span class="status status-{{ $ticket->status->value }}">{{ $ticket->status->label() }}</span></td>
                            <td class="{{ $ticket->priority === \App\Enums\TicketPriority::High ? 'priority-high' : '' }}">{{ $ticket->priority->label() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No tickets yet. Create one to get started.</p>
        @endif
    </div>
@endsection
