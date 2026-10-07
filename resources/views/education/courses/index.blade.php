@extends('layouts.education')

@section('title', 'Courses')

@section('content')
    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
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
            <div class="card-list-item">
                <div>
                    <a href="{{ route('education.courses.show', $course) }}"><strong>{{ $course->title }}</strong></a>
                    <div class="muted">{{ $course->code ? $course->code.' · ' : '' }}{{ $course->teacher->name ?? 'Instructor' }}</div>
                </div>
                <div class="muted">{{ $course->enrollments_count ?? $course->enrollments->count() }} enrolled</div>
            </div>
        @empty
            <p class="empty">No courses yet.</p>
        @endforelse
        <div class="pagination">{{ $courses->links('pagination::simple-default') }}</div>
    </div>
@endsection
