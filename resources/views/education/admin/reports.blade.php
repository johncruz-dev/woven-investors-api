@extends('layouts.education')

@section('title', 'Reports')

@section('content')
    <p class="muted"><a href="{{ route('education.admin.users.index') }}">← Admin</a></p>
    <div class="toolbar">
        <div>
            <h1>Platform reports</h1>
            <p class="subtitle">Live snapshot of users, learning activity, support, and subscriptions</p>
        </div>
        <div class="inline-actions">
            <a class="btn btn-secondary" href="{{ route('education.admin.subscriptions.index') }}">Subscriptions</a>
            <a class="btn btn-secondary" href="{{ route('education.admin.users.index') }}">Users</a>
        </div>
    </div>

    <h2 class="section-title">Overview</h2>
    <div class="metrics">
        <div class="metric"><div class="label">Users</div><div class="value">{{ $report['overview']['users'] }}</div></div>
        <div class="metric"><div class="label">Active users</div><div class="value">{{ $report['overview']['active_users'] }}</div></div>
        <div class="metric"><div class="label">Roster students</div><div class="value">{{ $report['overview']['roster_students'] }}</div></div>
        <div class="metric"><div class="label">Courses</div><div class="value">{{ $report['overview']['published_courses'] }}/{{ $report['overview']['courses'] }}</div></div>
        <div class="metric"><div class="label">Active enrollments</div><div class="value">{{ $report['overview']['active_enrollments'] }}</div></div>
    </div>

    <h2 class="section-title">Learning activity</h2>
    <div class="metrics">
        <div class="metric"><div class="label">Lessons completed</div><div class="value">{{ $report['learning']['lessons_completed'] }}</div></div>
        <div class="metric"><div class="label">Submissions</div><div class="value">{{ $report['learning']['submissions'] }}</div></div>
        <div class="metric"><div class="label">Pending grading</div><div class="value">{{ $report['learning']['pending_grading'] }}</div></div>
        <div class="metric"><div class="label">Avg score</div><div class="value">{{ $report['learning']['average_score'] ?: '—' }}</div></div>
        <div class="metric"><div class="label">Unread messages</div><div class="value">{{ $report['learning']['unread_messages'] }}</div></div>
    </div>

    <h2 class="section-title">Support & subscriptions</h2>
    <div class="metrics">
        <div class="metric"><div class="label">Open tickets</div><div class="value">{{ $report['support']['open_tickets'] }}</div></div>
        <div class="metric"><div class="label">High priority open</div><div class="value">{{ $report['support']['high_priority_open'] }}</div></div>
        <div class="metric"><div class="label">Resolved tickets</div><div class="value">{{ $report['support']['resolved_tickets'] }}</div></div>
        <div class="metric"><div class="label">Active subscriptions</div><div class="value">{{ $report['subscriptions']['active'] }}</div></div>
        <div class="metric"><div class="label">Subscription revenue</div><div class="value" style="font-size:1.2rem;">£{{ number_format($report['subscriptions']['monthly_revenue'], 2) }}</div></div>
    </div>

    <div class="report-grid">
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Users by role</h2>
            <table>
                <thead><tr><th>Role</th><th>Count</th></tr></thead>
                <tbody>
                    @foreach ($report['users_by_role'] as $row)
                        <tr>
                            <td>{{ $row['role'] }}</td>
                            <td>{{ $row['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Tickets by status</h2>
            <table>
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody>
                    @foreach ($report['ticket_breakdown'] as $row)
                        <tr>
                            <td><span class="status status-{{ $row['value'] }}">{{ $row['status'] }}</span></td>
                            <td>{{ $row['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Top courses by enrollment</h2>
        @if ($report['top_courses']->count())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Enrollments</th>
                            <th>Lessons</th>
                            <th>Assignments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['top_courses'] as $course)
                            <tr>
                                <td><a href="{{ route('education.courses.show', $course) }}">{{ $course->title }}</a></td>
                                <td>{{ $course->teacher->name ?? '—' }}</td>
                                <td>{{ $course->active_enrollments_count }}</td>
                                <td>{{ $course->published_lessons_count }}</td>
                                <td>{{ $course->published_assignments_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="empty">No courses yet.</p>
        @endif
    </div>

    <div class="report-grid">
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Pending grading</h2>
            @forelse ($report['pending_grading'] as $submission)
                <div class="lesson-row">
                    <div class="lesson-row-main">
                        <a href="{{ route('education.assignments.show', $submission->assignment) }}">{{ $submission->assignment->title }}</a>
                        <span class="muted">{{ $submission->student->name }}</span>
                        <span class="muted">{{ $submission->assignment->course->title ?? '' }}</span>
                    </div>
                    <span class="status status-submitted">Submitted</span>
                </div>
            @empty
                <p class="empty">No submissions waiting for grades.</p>
            @endforelse
        </div>

        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Open support tickets</h2>
            @forelse ($report['open_tickets'] as $ticket)
                <div class="lesson-row">
                    <div class="lesson-row-main">
                        <a href="{{ route('education.tickets.show', $ticket) }}">{{ $ticket->subject }}</a>
                        <span class="muted">{{ $ticket->student->name ?? '—' }}</span>
                        @if ($ticket->assignee)
                            <span class="muted">Assigned: {{ $ticket->assignee->name }}</span>
                        @endif
                    </div>
                    <span class="status status-{{ $ticket->status->value }}">{{ $ticket->status->label() }}</span>
                </div>
            @empty
                <p class="empty">No open tickets.</p>
            @endforelse
        </div>
    </div>
@endsection
