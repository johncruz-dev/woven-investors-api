@extends('layouts.education')

@section('title', $scheduled->title)

@section('content')
    <p class="muted"><a href="{{ route('education.schedule.index') }}">← Schedule</a></p>

    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
            <p class="eyebrow">{{ $scheduled->course->title }}</p>
            <h1>{{ $scheduled->title }}</h1>
            <p class="subtitle">
                {{ $scheduled->starts_at->format('l, M j Y · H:i') }}
                – {{ $scheduled->ends_at->format('H:i') }}
                · with {{ $scheduled->teacher->name }}
            </p>
        </div>
        <div class="inline-actions">
            @if ($scheduled->status === 'cancelled')
                <span class="status status-overdue">Cancelled</span>
            @elseif ($scheduled->isLive())
                <span class="status status-submitted">Live now</span>
            @elseif ($scheduled->isUpcoming())
                <span class="status status-graded">Upcoming</span>
            @endif

            @if ($scheduled->canJoinNow())
                <a class="btn btn-primary" href="{{ $scheduled->meeting_url }}" target="_blank" rel="noopener noreferrer">
                    Join video call
                </a>
            @elseif ($scheduled->isScheduled())
                <a class="btn btn-secondary" href="{{ $scheduled->meeting_url }}" target="_blank" rel="noopener noreferrer">
                    Open meeting link
                </a>
            @endif

            @if ($user->canCreateCourses() && ($user->isAdmin() || $scheduled->teacher_id === $user->id) && $scheduled->isScheduled())
                <form method="POST" action="{{ route('education.schedule.destroy', $scheduled) }}" onsubmit="return confirm('Cancel this video lesson?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Cancel lesson</button>
                </form>
            @endif
        </div>
    </div>

    <div class="roster-layout">
        <div class="card">
            <h2>Session details</h2>
            @if ($scheduled->description)
                <p style="margin-top:0.75rem;white-space:pre-wrap;">{{ $scheduled->description }}</p>
            @else
                <p class="muted" style="margin-top:0.75rem;">No extra notes for this session.</p>
            @endif

            <div class="schedule-meta">
                <div>
                    <span class="muted">Teacher</span>
                    <strong>{{ $scheduled->teacher->name }}</strong>
                </div>
                <div>
                    <span class="muted">Course</span>
                    <strong><a href="{{ route('education.courses.show', $scheduled->course) }}">{{ $scheduled->course->title }}</a></strong>
                </div>
                @if ($scheduled->lesson)
                    <div>
                        <span class="muted">Reading lesson</span>
                        <strong>
                            <a href="{{ route('education.lessons.show', [$scheduled->course, $scheduled->lesson]) }}">
                                {{ $scheduled->lesson->title }}
                            </a>
                        </strong>
                    </div>
                @endif
                <div>
                    <span class="muted">Attendance</span>
                    <strong>{{ $confirmedCount }} confirmed · {{ $declinedCount }} declined</strong>
                </div>
            </div>

            @if ($user->isStudent() && $scheduled->isScheduled() && $scheduled->ends_at->isFuture())
                <div class="attendance-box">
                    <h2>Your attendance</h2>
                    @if ($attendance?->isConfirmed())
                        <p class="muted">You’re confirmed for this video call.</p>
                        <span class="status status-graded">Attending</span>
                    @elseif ($attendance?->isDeclined())
                        <p class="muted">You marked that you can’t attend.</p>
                        <span class="status status-overdue">Not attending</span>
                    @else
                        <p class="muted">Please confirm if you will join the video call.</p>
                    @endif

                    <div class="inline-actions" style="margin-top:0.85rem;">
                        <form method="POST" action="{{ route('education.schedule.confirm', $scheduled) }}">
                            @csrf
                            <button class="btn btn-primary" type="submit" @disabled($attendance?->isConfirmed())>
                                Confirm I’ll attend
                            </button>
                        </form>
                        <form method="POST" action="{{ route('education.schedule.decline', $scheduled) }}">
                            @csrf
                            <button class="btn btn-secondary" type="submit" @disabled($attendance?->isDeclined())>
                                Can’t attend
                            </button>
                        </form>
                    </div>
                    @error('attendance')<p class="error">{{ $message }}</p>@enderror
                </div>
            @endif

            @unless ($scheduled->canJoinNow())
                <p class="muted field-hint" style="margin-top:1rem;">
                    The Join button opens 15 minutes before the start time.
                </p>
            @endunless
        </div>

        <aside class="card">
            <h2>Reading materials</h2>
            <p class="muted profile-section-intro">Review these before or during the video call.</p>

            @if ($scheduled->lesson)
                <a class="btn btn-secondary" style="width:100%;margin:0.85rem 0;" href="{{ route('education.lessons.show', [$scheduled->course, $scheduled->lesson]) }}">
                    Open reading lesson
                </a>
            @endif

            @forelse ($materials as $material)
                <div class="card-list-item">
                    <div>
                        <strong>{{ $material->title }}</strong>
                        <div class="muted">{{ $material->original_name }}</div>
                    </div>
                    <a class="btn btn-secondary" href="{{ route('education.materials.download', $material) }}">Download</a>
                </div>
            @empty
                <p class="empty" style="margin-top:1rem;">No files attached. Ask your teacher if you need materials.</p>
            @endforelse

            @if ($user->canCreateCourses() || $user->isAdmin())
                <h2 style="margin-top:1.5rem;">Invited students</h2>
                @php
                    $attendanceByUser = $scheduled->attendances->keyBy('user_id');
                @endphp
                @forelse ($scheduled->participants as $participant)
                    @php $row = $attendanceByUser->get($participant->id); @endphp
                    <div class="card-list-item">
                        <div>
                            <strong>{{ $participant->name }}</strong>
                            <div class="muted">{{ $participant->email }}</div>
                        </div>
                        @if ($row?->isConfirmed())
                            <span class="status status-graded">Confirmed</span>
                        @elseif ($row?->isDeclined())
                            <span class="status status-overdue">Declined</span>
                        @else
                            <span class="status status-not_started">Pending</span>
                        @endif
                    </div>
                @empty
                    <p class="empty" style="margin-top:0.75rem;">No invited students listed.</p>
                @endforelse
            @endif
        </aside>
    </div>
@endsection
