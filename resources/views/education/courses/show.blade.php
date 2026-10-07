@extends('layouts.education')

@section('title', $course->title)

@section('content')
    <p class="muted"><a href="{{ route('education.courses.index') }}">← Courses</a></p>
    <div class="toolbar">
        <div>
            <h1>{{ $course->title }}</h1>
            <p class="subtitle">{{ $course->code }} · Instructor: {{ $course->teacher->name }}</p>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            @if ($user->canEnrollInCourses() && ! $isEnrolled && $course->isPublished())
                <form method="POST" action="{{ route('education.courses.enroll', $course) }}">@csrf<button class="btn btn-primary" type="submit">Enroll</button></form>
            @endif
            @if ($isOwner && $user->canManageCourseStudents())
                <a class="btn btn-secondary" href="{{ route('education.courses.students', $course) }}">Manage students</a>
            @endif
        </div>
    </div>

    @if ($course->description)
        <div class="card"><p>{{ $course->description }}</p></div>
    @endif

    @if ($progress)
        <div class="metrics">
            <div class="metric"><div class="label">Lessons completed</div><div class="value">{{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }}</div></div>
            <div class="metric"><div class="label">Lesson progress</div><div class="value">{{ $progress['lesson_percent'] }}%</div></div>
            <div class="metric"><div class="label">Assignments done</div><div class="value">{{ $progress['assignments_submitted'] }}/{{ $progress['assignments_total'] }}</div></div>
            <div class="metric"><div class="label">Avg score</div><div class="value">{{ $progress['average_score'] !== null ? round($progress['average_score'], 1) : '—' }}</div></div>
        </div>
        <div class="progress-panel card">
            <div class="progress-track" aria-hidden="true">
                <div class="progress-fill" style="width: {{ min(100, $progress['lesson_percent']) }}%;"></div>
            </div>
            <p class="muted">Course completion based on published lessons</p>
        </div>
    @endif

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Lessons</h2>
        @forelse ($course->lessons as $lesson)
            @php
                $canOpenLesson = ($isEnrolled || $isOwner) && ($lesson->is_published || $isOwner);
                $lessonDone = in_array($lesson->id, $completedLessonIds, true);
            @endphp
            <div class="lesson-row">
                <div class="lesson-row-main">
                    @if ($canOpenLesson)
                        <a href="{{ route('education.lessons.show', [$course, $lesson]) }}">{{ $lesson->position }}. {{ $lesson->title }}</a>
                    @else
                        <span>{{ $lesson->position }}. {{ $lesson->title }}</span>
                    @endif
                    @unless ($lesson->is_published)
                        <span class="badge-warn">Draft</span>
                    @endunless
                    @if ($lessonDone)
                        <span class="status status-graded">Completed</span>
                    @elseif ($progress && $lesson->is_published)
                        <span class="status status-not_started">Not started</span>
                    @endif
                </div>
                @if ($isOwner && $user->canCreateCourses())
                    <div class="inline-actions">
                        <form method="POST" action="{{ route('education.lessons.move', [$course, $lesson]) }}">
                            @csrf
                            <input type="hidden" name="direction" value="up">
                            <button class="btn btn-ghost" type="submit" title="Move up" @disabled($loop->first)>↑</button>
                        </form>
                        <form method="POST" action="{{ route('education.lessons.move', [$course, $lesson]) }}">
                            @csrf
                            <input type="hidden" name="direction" value="down">
                            <button class="btn btn-ghost" type="submit" title="Move down" @disabled($loop->last)>↓</button>
                        </form>
                        <a class="btn btn-ghost" href="{{ route('education.lessons.edit', [$course, $lesson]) }}">Edit</a>
                        <form method="POST" action="{{ route('education.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('Delete this lesson?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-ghost danger" type="submit">Delete</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="muted">No lessons yet.</p>
        @endforelse

        @if ($isOwner && $user->canCreateCourses())
            <form method="POST" action="{{ route('education.lessons.store', $course) }}" style="margin-top:1rem;">
                @csrf
                <div class="field"><label>Add lesson title</label><input type="text" name="title" required></div>
                <div class="field"><label>Content</label><textarea name="content" rows="3"></textarea></div>
                <button class="btn btn-secondary" type="submit">Add lesson</button>
            </form>
        @endif
    </div>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Learning materials</h2>
        @forelse ($course->materials as $material)
            <div style="padding:0.4rem 0;">
                <a href="{{ route('education.materials.download', $material) }}">{{ $material->title }}</a>
                <span class="muted">({{ $material->original_name }})</span>
            </div>
        @empty
            <p class="muted">No materials uploaded.</p>
        @endforelse

        @if ($isOwner && $user->canUploadLearningMaterials())
            <form method="POST" action="{{ route('education.materials.store', $course) }}" enctype="multipart/form-data" style="margin-top:1rem;">
                @csrf
                <div class="field"><label>Title</label><input type="text" name="title"></div>
                <div class="field"><label>File</label><input type="file" name="file" required></div>
                <button class="btn btn-secondary" type="submit">Upload material</button>
            </form>
        @endif
    </div>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Assignments / assessments</h2>
        @forelse ($course->assignments as $assignment)
            @php
                $assignmentState = $assignmentStatusById->get($assignment->id);
                $assignmentStatus = $assignmentState['status'] ?? null;
            @endphp
            <div class="lesson-row">
                <div class="lesson-row-main">
                    @if ($isEnrolled || $isOwner)
                        <a href="{{ route('education.assignments.show', $assignment) }}">{{ $assignment->title }}</a>
                    @else
                        <span>{{ $assignment->title }}</span>
                    @endif
                    <span class="muted">{{ $assignment->max_points }} pts</span>
                    @if ($assignment->due_at)
                        <span class="muted">Due {{ $assignment->due_at->format('d M Y') }}</span>
                    @endif
                    @if ($assignmentStatus)
                        <span class="status status-{{ $assignmentStatus }}">
                            @switch($assignmentStatus)
                                @case('graded') Graded @if($assignmentState['score'] !== null) · {{ $assignmentState['score'] }}/{{ $assignment->max_points }} @endif @break
                                @case('submitted') Submitted @break
                                @case('overdue') Overdue @break
                                @default Not started
                            @endswitch
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <p class="muted">No assessments yet.</p>
        @endforelse

        @if ($isOwner && $user->canCreateAssessments())
            <form method="POST" action="{{ route('education.assignments.store', $course) }}" style="margin-top:1rem;">
                @csrf
                <div class="field"><label>Assessment title</label><input type="text" name="title" required></div>
                <div class="field"><label>Instructions</label><textarea name="instructions" rows="3"></textarea></div>
                <div class="field"><label>Max points</label><input type="number" name="max_points" value="100" min="1"></div>
                <div class="field"><label>Due at</label><input type="datetime-local" name="due_at"></div>
                <button class="btn btn-secondary" type="submit">Create assessment</button>
            </form>
        @endif
    </div>

    @if ($isOwner && count($performance))
        <div class="card">
            <h2 style="font-size:1rem;margin-bottom:1rem;">Student performance</h2>
            <table>
                <thead><tr><th>Student</th><th>Lessons</th><th>Assignments</th><th>Avg score</th></tr></thead>
                <tbody>
                    @foreach ($performance as $row)
                        <tr>
                            <td>{{ $row['student']->name }}</td>
                            <td>{{ $row['progress']['lessons_completed'] }}/{{ $row['progress']['lessons_total'] }} ({{ $row['progress']['lesson_percent'] }}%)</td>
                            <td>{{ $row['progress']['assignments_submitted'] }}/{{ $row['progress']['assignments_total'] }}</td>
                            <td>{{ $row['progress']['average_score'] !== null ? round($row['progress']['average_score'], 1) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
