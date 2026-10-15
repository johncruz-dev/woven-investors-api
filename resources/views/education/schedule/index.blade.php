@extends('layouts.education')

@section('title', 'Schedule')

@section('content')
    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
            <p class="eyebrow">Live learning</p>
            <h1>Lesson schedule</h1>
            <p class="subtitle">Video lessons with teachers, plus reading materials for each session</p>
        </div>
        <div class="inline-actions">
            @if ($user->canCreateCourses())
                <button type="button" class="btn btn-primary" data-modal-open="schedule-lesson-modal">Schedule lesson</button>
            @endif
        </div>
    </div>

    @if ($upcoming->isNotEmpty())
        <div class="card upcoming-strip">
            <h2>Coming up</h2>
            <div class="upcoming-list">
                @foreach ($upcoming as $item)
                    <a href="{{ route('education.schedule.show', $item) }}" class="upcoming-item {{ $item->canJoinNow() ? 'is-live' : '' }}">
                        <div>
                            <strong>{{ $item->title }}</strong>
                            <div class="muted">{{ $item->course->title }} · {{ $item->teacher->name }}</div>
                        </div>
                        <div class="upcoming-when">
                            @if ($item->isLive())
                                <span class="status status-submitted">Live now</span>
                            @elseif ($item->canJoinNow())
                                <span class="status status-graded">Join soon</span>
                            @endif
                            <span>{{ $item->starts_at->format('D M j · H:i') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card calendar-card">
        <div class="calendar-toolbar">
            <a class="btn btn-secondary" href="{{ route('education.schedule.index', ['month' => $prev->month, 'year' => $prev->year]) }}">‹ Prev</a>
            <h2>{{ $cursor->format('F Y') }}</h2>
            <a class="btn btn-secondary" href="{{ route('education.schedule.index', ['month' => $next->month, 'year' => $next->year]) }}">Next ›</a>
        </div>

        <div class="calendar-grid">
            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $label)
                <div class="calendar-dow">{{ $label }}</div>
            @endforeach

            @foreach ($days as $day)
                <div class="calendar-day {{ $day['in_month'] ? '' : 'is-outside' }} {{ $day['is_today'] ? 'is-today' : '' }}">
                    <div class="calendar-day-num">{{ $day['date']->day }}</div>
                    <div class="calendar-events">
                        @foreach ($day['lessons']->take(3) as $lesson)
                            <a href="{{ route('education.schedule.show', $lesson) }}" class="calendar-event" title="{{ $lesson->title }}">
                                <span>{{ $lesson->starts_at->format('H:i') }}</span>
                                {{ \Illuminate\Support\Str::limit($lesson->title, 18) }}
                            </a>
                        @endforeach
                        @if ($day['lessons']->count() > 3)
                            <div class="muted calendar-more">+{{ $day['lessons']->count() - 3 }} more</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($user->canCreateCourses())
        @php
            $openScheduleModal = $errors->hasAny(['course_id', 'title', 'starts_at', 'ends_at', 'meeting_url', 'lesson_id', 'student_ids']) || old('title') !== null;
        @endphp
        <div class="modal {{ $openScheduleModal ? 'is-open' : '' }}" id="schedule-lesson-modal" data-modal aria-hidden="{{ $openScheduleModal ? 'false' : 'true' }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="schedule-lesson-title">
                <div class="modal-header">
                    <h2 id="schedule-lesson-title">Schedule video lesson</h2>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close">✕</button>
                </div>
                <form method="POST" action="{{ route('education.schedule.store') }}" class="modal-body">
                    @csrf
                    <div class="field">
                        <label for="course_id">Course</label>
                        <select id="course_id" name="course_id" required>
                            <option value="">Select course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>
                                    {{ $course->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <div class="student-picker-head">
                            <label>Invite students</label>
                            <button type="button" class="btn btn-ghost" id="select-all-students">Select all in course</button>
                        </div>
                        <div class="student-picker" id="student-picker">
                            <p class="muted student-picker-empty">Select a course to choose students.</p>
                            @foreach ($courses as $course)
                                <div class="student-picker-group" data-course-students="{{ $course->id }}" hidden>
                                    @forelse ($course->enrollments as $enrollment)
                                        @if ($enrollment->student)
                                            <label class="student-picker-option">
                                                <input
                                                    type="checkbox"
                                                    name="student_ids[]"
                                                    value="{{ $enrollment->student->id }}"
                                                    data-course="{{ $course->id }}"
                                                    @checked(collect(old('student_ids', []))->contains($enrollment->student->id))
                                                >
                                                <span>
                                                    <strong>{{ $enrollment->student->name }}</strong>
                                                    <small>{{ $enrollment->student->email }}</small>
                                                </span>
                                            </label>
                                        @endif
                                    @empty
                                        <p class="muted">No enrolled students in this course yet.</p>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                        <p class="muted field-hint">Only selected students will see this meeting and get notified.</p>
                        @error('student_ids')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="title">Lesson title</label>
                        <input id="title" type="text" name="title" value="{{ old('title') }}" required placeholder="Live algebra workshop">
                        @error('title')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="profile-grid">
                        <div class="field">
                            <label for="starts_at">Starts</label>
                            <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required>
                            @error('starts_at')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="ends_at">Ends</label>
                            <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required>
                            @error('ends_at')<p class="error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="field">
                        <label for="meeting_url">Video call link</label>
                        <input id="meeting_url" type="url" name="meeting_url" value="{{ old('meeting_url') }}" required placeholder="https://meet.google.com/...">
                        <p class="muted field-hint">Paste a Zoom, Google Meet, or Jitsi link</p>
                        @error('meeting_url')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="lesson_id">Reading material lesson (optional)</label>
                        <select id="lesson_id" name="lesson_id">
                            <option value="">Course materials only</option>
                            @foreach ($courses as $course)
                                @foreach ($course->lessons as $lesson)
                                    <option value="{{ $lesson->id }}" data-course="{{ $course->id }}" @selected((string) old('lesson_id') === (string) $lesson->id)>
                                        {{ $course->title }} — {{ $lesson->title }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('lesson_id')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="description">Notes for students</label>
                        <textarea id="description" name="description" rows="3" placeholder="Bring questions from the reading">{{ old('description') }}</textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                        <button class="btn btn-primary" type="submit">Schedule lesson</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.querySelector('[data-modal]');
    if (!modal) return;

    const open = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        modal.querySelector('input, select, textarea, button')?.focus();
    };
    const close = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('[data-modal-open="schedule-lesson-modal"]').forEach((btn) => {
        btn.addEventListener('click', open);
    });
    modal.querySelectorAll('[data-modal-close]').forEach((el) => el.addEventListener('click', close));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) close();
    });

    const courseSelect = document.getElementById('course_id');
    const emptyState = document.querySelector('.student-picker-empty');
    const groups = document.querySelectorAll('[data-course-students]');
    const selectAllBtn = document.getElementById('select-all-students');

    const syncStudents = () => {
        const courseId = courseSelect?.value || '';
        groups.forEach((group) => {
            const match = group.getAttribute('data-course-students') === courseId;
            group.hidden = !match;
            group.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                input.disabled = !match;
                if (!match) input.checked = false;
            });
        });
        if (emptyState) emptyState.hidden = Boolean(courseId);
    };

    courseSelect?.addEventListener('change', syncStudents);
    selectAllBtn?.addEventListener('click', () => {
        const courseId = courseSelect?.value;
        if (!courseId) return;
        document.querySelectorAll(`input[type="checkbox"][data-course="${courseId}"]`).forEach((input) => {
            input.checked = true;
        });
    });
    syncStudents();
})();
</script>
@endpush
