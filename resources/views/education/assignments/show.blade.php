@extends('layouts.education')

@section('title', $assignment->title)

@section('content')
    <p class="muted"><a href="{{ route('education.courses.show', $assignment->course) }}">← {{ $assignment->course->title }}</a></p>
    <h1>{{ $assignment->title }}</h1>
    <p class="subtitle">{{ $assignment->max_points }} points @if($assignment->due_at)· Due {{ $assignment->due_at->format('d M Y H:i') }}@endif</p>

    <div class="card" style="white-space:pre-wrap;">{{ $assignment->instructions ?: 'No instructions.' }}</div>

    @if ($user->canSubmitHomework())
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Submit homework</h2>
            @if ($ownSubmission)
                <p class="muted">Last submitted {{ $ownSubmission->submitted_at?->format('d M Y H:i') }} · Status: {{ $ownSubmission->status }}
                    @if($ownSubmission->isGraded()) · Score: {{ $ownSubmission->score }}/{{ $assignment->max_points }}@endif
                </p>
                @if ($ownSubmission->feedback)
                    <p><strong>Feedback:</strong> {{ $ownSubmission->feedback }}</p>
                @endif
            @endif
            <form method="POST" action="{{ route('education.assignments.submit', $assignment) }}" enctype="multipart/form-data">
                @csrf
                <div class="field"><label>Your answer</label><textarea name="content" rows="5">{{ old('content', $ownSubmission?->content) }}</textarea></div>
                <div class="field"><label>Attachment (optional)</label><input type="file" name="file"></div>
                <button class="btn btn-primary" type="submit">Submit homework</button>
            </form>
        </div>
    @endif

    @if ($user->canGradeAssignments())
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Submissions</h2>
            @forelse ($assignment->submissions as $submission)
                <div style="border-bottom:1px solid var(--border);padding:0.75rem 0;">
                    <strong>{{ $submission->student->name }}</strong>
                    <span class="muted">· {{ $submission->status }}</span>
                    <p style="margin:0.5rem 0;white-space:pre-wrap;">{{ $submission->content }}</p>
                    <form method="POST" action="{{ route('education.submissions.grade', $submission) }}" class="toolbar">
                        @csrf
                        <div><label>Score</label><input type="number" name="score" min="0" max="{{ $assignment->max_points }}" value="{{ $submission->score }}" required></div>
                        <div style="flex:1;"><label>Feedback</label><input type="text" name="feedback" value="{{ $submission->feedback }}"></div>
                        <button class="btn btn-secondary" type="submit">Grade</button>
                    </form>
                </div>
            @empty
                <p class="empty">No submissions yet.</p>
            @endforelse
        </div>
    @endif
@endsection
