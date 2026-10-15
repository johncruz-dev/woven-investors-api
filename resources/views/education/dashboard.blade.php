@extends('layouts.education')

@section('title', 'Dashboard')

@section('content')
    <section class="page-hero">
        <div class="page-hero-copy">
            <p class="eyebrow">{{ now()->format('l, M j') }}</p>
            <h1>
                @if ($user->isStudent())
                    Welcome back, {{ explode(' ', $user->name)[0] }}
                @else
                    {{ $school?->name ?? 'Woven Education' }}
                @endif
            </h1>
            <p class="subtitle">
                @if ($user->isStudent())
                    Pick up where you left off across your courses and support requests.
                @elseif ($school)
                    Overview of students, tickets, and learning activity for {{ $school->name }}.
                @else
                    No school configured yet. Run <code>php artisan db:seed</code>.
                @endif
            </p>
        </div>
        <div class="page-hero-actions">
            <a class="btn btn-primary" href="{{ route('education.courses.index') }}">Browse courses</a>
            <a class="btn btn-secondary" href="{{ route('education.tickets.create') }}">New ticket</a>
        </div>
    </section>

    @if ($user->isStudent())
        @if (count($learning))
            <div class="metrics">
                <div class="metric metric-accent">
                    <div class="metric-top">
                        <div class="label">Enrolled courses</div>
                        <span class="metric-dot"></span>
                    </div>
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

            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h2>Course progress</h2>
                        <p class="muted">Your current learning momentum</p>
                    </div>
                </div>
                <div class="card panel-card">
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
            </section>
        @else
            <div class="empty-state card">
                <div class="empty-state-icon" aria-hidden="true">
                    <x-icon name="courses" class="icon" />
                </div>
                <h2>No courses yet</h2>
                <p class="muted">Enroll in a course to start tracking lessons and assignments here.</p>
                <a class="btn btn-primary" href="{{ route('education.courses.index') }}">Browse courses</a>
            </div>
        @endif
    @else
        <div class="metrics">
            <div class="metric metric-accent">
                <div class="metric-top">
                    <div class="label">Students</div>
                    <span class="metric-dot"></span>
                </div>
                <div class="value">{{ $metrics['students'] }}</div>
            </div>
            <div class="metric metric-warning">
                <div class="metric-top">
                    <div class="label">Open tickets</div>
                    <span class="metric-dot"></span>
                </div>
                <div class="value">{{ $metrics['open_tickets'] }}</div>
            </div>
            <div class="metric metric-success">
                <div class="metric-top">
                    <div class="label">Resolved</div>
                    <span class="metric-dot"></span>
                </div>
                <div class="value">{{ $metrics['resolved_tickets'] }}</div>
            </div>
            <div class="metric">
                <div class="metric-top">
                    <div class="label">High priority open</div>
                    <span class="metric-dot"></span>
                </div>
                <div class="value">{{ $metrics['high_priority_open'] }}</div>
            </div>
        </div>
    @endif

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2>Quick links</h2>
                <p class="muted">Jump into the areas you use most</p>
            </div>
        </div>
        <div class="quick-grid">
            <a class="quick-card" href="{{ route('education.courses.index') }}">
                <span class="quick-icon"><x-icon name="courses" class="icon" /></span>
                <strong>Courses</strong>
                <span>Browse and manage learning content</span>
            </a>
            @if ($user->canGradeAssignments())
                <a class="quick-card" href="{{ route('education.assignments.queue') }}">
                    <span class="quick-icon"><x-icon name="grading" class="icon" /></span>
                    <strong>Grading queue</strong>
                    <span>Review submitted assignments</span>
                </a>
            @endif
            @if ($user->isEducationStaff())
                <a class="quick-card" href="{{ route('education.students.index') }}">
                    <span class="quick-icon"><x-icon name="students" class="icon" /></span>
                    <strong>Students</strong>
                    <span>Open the student directory</span>
                </a>
            @endif
            <a class="quick-card" href="{{ route('education.tickets.create') }}">
                <span class="quick-icon"><x-icon name="support" class="icon" /></span>
                <strong>New ticket</strong>
                <span>Create a support request</span>
            </a>
            @if ($user->canManageUsersAndPermissions())
                <a class="quick-card" href="{{ route('education.admin.users.index') }}">
                    <span class="quick-icon"><x-icon name="admin" class="icon" /></span>
                    <strong>Admin panel</strong>
                    <span>Users, reports, and settings</span>
                </a>
            @endif
        </div>
    </section>

    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
            <p class="eyebrow">Support</p>
            <h2>Recent support tickets</h2>
            <p class="subtitle">Latest activity across the school</p>
        </div>
        <div class="inline-actions">
            <a class="btn btn-secondary" href="{{ route('education.tickets.index') }}">View all</a>
            <a class="btn btn-primary" href="{{ route('education.tickets.create') }}">New ticket</a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTickets ?? [] as $ticket)
                        <tr>
                            <td>{{ $ticket->subject }}</td>
                            <td>{{ $ticket->student->name }}</td>
                            <td><span class="status status-{{ $ticket->status->value }}">{{ $ticket->status->label() }}</span></td>
                            <td class="{{ $ticket->priority === \App\Enums\TicketPriority::High ? 'priority-high' : '' }}">
                                {{ $ticket->priority->label() }}
                            </td>
                            <td class="table-actions">
                                <a class="btn btn-secondary" href="{{ route('education.tickets.show', $ticket) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">No tickets yet. Create one to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($recentTickets)
            @include('partials.pagination', ['paginator' => $recentTickets])
        @endif
    </div>
@endsection
