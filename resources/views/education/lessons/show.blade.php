@extends('layouts.education')

@section('title', $lesson->title)

@section('content')
    <p class="muted"><a href="{{ route('education.courses.show', $course) }}">← {{ $course->title }}</a></p>

    <div class="toolbar">
        <div>
            <h1>{{ $lesson->title }}</h1>
            <p class="subtitle">
                Lesson {{ $lesson->position }}
                @unless ($lesson->is_published)
                    · <span class="badge-warn">Draft</span>
                @endunless
            </p>
        </div>
        @if ($isOwner && $user->canCreateCourses())
            <div class="inline-actions">
                <a class="btn btn-secondary" href="{{ route('education.lessons.edit', [$course, $lesson]) }}">Edit</a>
                <form method="POST" action="{{ route('education.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('Delete this lesson?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Delete</button>
                </form>
            </div>
        @endif
    </div>

    <div class="card" style="white-space:pre-wrap;">{{ $lesson->content ?: 'No content yet.' }}</div>

    @if ($lesson->materials->count())
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:0.75rem;">Materials</h2>
            @foreach ($lesson->materials as $material)
                <div><a href="{{ route('education.materials.download', $material) }}">{{ $material->title }}</a></div>
            @endforeach
        </div>
    @endif

    @if ($user->canTrackLearningProgress() && $user->isStudent() && $lesson->is_published)
        @php
            $completed = $lesson->progressRecords()
                ->where('student_id', $user->id)
                ->where('completed', true)
                ->exists();
        @endphp
        @if ($completed)
            <p><span class="status status-graded">Completed</span></p>
        @else
            <form method="POST" action="{{ route('education.lessons.complete', [$course, $lesson]) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Mark lesson complete</button>
            </form>
        @endif
    @endif
@endsection
