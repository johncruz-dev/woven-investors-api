@extends('layouts.education')

@section('title', 'Courses')

@section('content')
    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
            <p class="eyebrow">Learning</p>
            <h1>Courses</h1>
            <p class="subtitle">
                @if ($user->canCreateCourses() && ! $user->isStudent())
                    Create courses and manage learning content
                @else
                    Browse courses, enroll, and track your learning
                @endif
            </p>
        </div>
        @if ($user->canCreateCourses() && ! $user->isStudent())
            <a class="btn btn-primary" href="{{ route('education.courses.create') }}">Create course</a>
        @endif
    </div>

    <div class="card">
        @forelse ($courses as $course)
            <a href="{{ route('education.courses.show', $course) }}" class="course-row">
                <div class="course-row-main">
                    <strong>{{ $course->title }}</strong>
                    <div class="muted">{{ $course->code ? $course->code.' · ' : '' }}{{ $course->teacher->name ?? 'Instructor' }}</div>
                </div>
                <div class="course-row-meta">
                    <span class="status status-graded">{{ $course->enrollments_count ?? $course->enrollments->count() }} enrolled</span>
                </div>
            </a>
        @empty
            <div class="empty-state compact">
                <div class="empty-state-icon" aria-hidden="true">
                    <x-icon name="courses" class="icon" />
                </div>
                <p class="empty">No courses yet.</p>
            </div>
        @endforelse
        <div class="pagination">{{ $courses->links('pagination::simple-default') }}</div>
    </div>
@endsection
