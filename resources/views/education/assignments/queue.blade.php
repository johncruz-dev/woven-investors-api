@extends('layouts.education')

@section('title', 'Grading queue')

@section('content')
    <div class="toolbar">
        <div>
            <h1>Grading queue</h1>
            <p class="subtitle">Pending submissions across your courses</p>
        </div>
        <form method="GET" class="inline-actions">
            <div class="field" style="margin-bottom:0;min-width:200px;">
                <label for="course_id">Course</label>
                <select id="course_id" name="course_id" onchange="this.form.submit()">
                    <option value="">All courses</option>
                    @foreach ($courses as $courseOption)
                        <option value="{{ $courseOption->id }}" @selected($courseId === $courseOption->id)>
                            {{ $courseOption->title }}{{ $courseOption->code ? ' ('.$courseOption->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="metrics">
        <div class="metric">
            <div class="label">Pending</div>
            <div class="value">{{ $submissions->total() }}</div>
        </div>
        <div class="metric">
            <div class="label">Courses</div>
            <div class="value">{{ $courses->count() }}</div>
        </div>
    </div>

    @forelse ($submissions as $submission)
        <div class="card grading-card">
            <div class="toolbar" style="margin-bottom:0.75rem;">
                <div>
                    <h2 style="font-size:1rem;margin:0;">
                        <a href="{{ route('education.assignments.show', $submission->assignment) }}">{{ $submission->assignment->title }}</a>
                    </h2>
                    <p class="muted" style="margin:0.35rem 0 0;">
                        {{ $submission->assignment->course->title }}
                        · {{ $submission->student->name }}
                        · Submitted {{ $submission->submitted_at?->format('d M Y H:i') ?? '—' }}
                        @if ($submission->assignment->due_at)
                            · Due {{ $submission->assignment->due_at->format('d M Y') }}
                            @if ($submission->assignment->due_at->isPast())
                                <span class="status status-overdue">Overdue</span>
                            @endif
                        @endif
                    </p>
                </div>
                <span class="status status-submitted">Submitted</span>
            </div>

            @if ($submission->content)
                <div class="grading-content">{{ $submission->content }}</div>
            @else
                <p class="muted">No written answer provided.</p>
            @endif

            @if ($submission->original_name)
                <p class="muted" style="margin-top:0.5rem;">Attachment: {{ $submission->original_name }}</p>
            @endif

            <form method="POST" action="{{ route('education.submissions.grade', $submission) }}" class="grading-form">
                @csrf
                <input type="hidden" name="redirect_to" value="queue">
                @if ($courseId)
                    <input type="hidden" name="course_id" value="{{ $courseId }}">
                @endif
                <div class="field">
                    <label>Score (max {{ $submission->assignment->max_points }})</label>
                    <input type="number" name="score" min="0" max="{{ $submission->assignment->max_points }}" required>
                </div>
                <div class="field" style="flex:1;">
                    <label>Feedback</label>
                    <input type="text" name="feedback" placeholder="Optional feedback">
                </div>
                <button class="btn btn-primary" type="submit">Grade</button>
            </form>
        </div>
    @empty
        <div class="card">
            <p class="empty">No pending submissions. You're all caught up.</p>
            <a class="btn btn-secondary" href="{{ route('education.courses.index') }}">Back to courses</a>
        </div>
    @endforelse

    <div class="pagination">{{ $submissions->links('pagination::simple-default') }}</div>
@endsection
