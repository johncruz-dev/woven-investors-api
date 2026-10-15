<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ScheduledLesson;
use App\Models\ScheduledLessonAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ScheduledLessonService
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function schedule(User $teacher, Course $course, array $data): ScheduledLesson
    {
        if (! $teacher->canCreateCourses()) {
            throw new InvalidArgumentException('Only teachers can schedule video lessons.');
        }

        if (! $teacher->isAdmin() && $course->teacher_id !== $teacher->id) {
            throw new InvalidArgumentException('You can only schedule lessons for your own courses.');
        }

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        if ($endsAt->lte($startsAt)) {
            throw new InvalidArgumentException('End time must be after the start time.');
        }

        if ($data['lesson_id'] ?? null) {
            $lessonBelongsToCourse = $course->lessons()->whereKey($data['lesson_id'])->exists();
            if (! $lessonBelongsToCourse) {
                throw new InvalidArgumentException('Selected reading lesson is not part of this course.');
            }
        }

        $studentIds = collect($data['student_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            throw new InvalidArgumentException('Select at least one enrolled student for this meeting.');
        }

        $enrolledIds = Enrollment::query()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id');

        if ($enrolledIds->count() !== $studentIds->count()) {
            throw new InvalidArgumentException('You can only invite students enrolled in this course.');
        }

        return DB::transaction(function () use ($teacher, $course, $data, $startsAt, $endsAt, $studentIds) {
            $scheduled = ScheduledLesson::query()->create([
                'course_id' => $course->id,
                'teacher_id' => $teacher->isAdmin() ? ($data['teacher_id'] ?? $course->teacher_id) : $teacher->id,
                'lesson_id' => $data['lesson_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'meeting_url' => $data['meeting_url'],
                'status' => 'scheduled',
            ]);

            $scheduled->participants()->sync($studentIds->all());

            $this->notifyParticipants(
                $scheduled,
                'lesson_scheduled',
                'Video lesson scheduled',
                "{$scheduled->title} is scheduled for {$scheduled->starts_at->format('D, M j · H:i')}."
            );

            return $scheduled;
        });
    }

    public function cancel(ScheduledLesson $scheduled, User $actor): ScheduledLesson
    {
        if (! $actor->isAdmin() && $scheduled->teacher_id !== $actor->id) {
            throw new InvalidArgumentException('You cannot cancel this lesson.');
        }

        $scheduled->update(['status' => 'cancelled']);

        $this->notifyParticipants(
            $scheduled,
            'lesson_cancelled',
            'Video lesson cancelled',
            "{$scheduled->title} on {$scheduled->starts_at->format('D, M j · H:i')} was cancelled."
        );

        return $scheduled->refresh();
    }

    /**
     * @return Collection<int, ScheduledLesson>
     */
    public function forUserInRange(User $user, Carbon $from, Carbon $to): Collection
    {
        $query = ScheduledLesson::query()
            ->with(['course:id,title,teacher_id', 'teacher:id,name', 'lesson:id,title,course_id'])
            ->where('status', 'scheduled')
            ->whereBetween('starts_at', [$from, $to]);

        if ($user->isAdmin()) {
            // Admins see all scheduled lessons.
        } elseif ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        } else {
            $query->where(function ($inner) use ($user) {
                $inner->whereHas('participants', function ($participants) use ($user) {
                    $participants->where('users.id', $user->id);
                })->orWhere(function ($legacy) use ($user) {
                    // Older lessons with no invite list: fall back to course enrollment.
                    $courseIds = Enrollment::query()
                        ->where('student_id', $user->id)
                        ->where('status', 'active')
                        ->pluck('course_id');

                    $legacy->whereDoesntHave('participants')
                        ->whereIn('course_id', $courseIds);
                });
            });
        }

        return $query->orderBy('starts_at')->get();
    }

    /**
     * @return Collection<int, ScheduledLesson>
     */
    public function upcomingForUser(User $user, int $limit = 5): Collection
    {
        return $this->forUserInRange($user, now()->subMinutes(15), now()->addDays(30))
            ->take($limit)
            ->values();
    }

    public function ensureUserCanAccess(User $user, ScheduledLesson $scheduled): void
    {
        if ($user->isAdmin() || $scheduled->teacher_id === $user->id) {
            return;
        }

        if ($user->isStudent()) {
            $isInvited = $scheduled->participants()->where('users.id', $user->id)->exists();

            if (! $isInvited && $scheduled->participants()->exists() === false) {
                $this->learningPlatformService->ensureUserCanAccessCourse($user, $scheduled->course);

                return;
            }

            if ($isInvited) {
                return;
            }
        }

        abort(403, 'You are not invited to this video lesson.');
    }

    public function confirmAttendance(User $user, ScheduledLesson $scheduled): ScheduledLessonAttendance
    {
        return $this->respondAttendance($user, $scheduled, 'confirmed');
    }

    public function declineAttendance(User $user, ScheduledLesson $scheduled): ScheduledLessonAttendance
    {
        return $this->respondAttendance($user, $scheduled, 'declined');
    }

    public function attendanceFor(User $user, ScheduledLesson $scheduled): ?ScheduledLessonAttendance
    {
        return ScheduledLessonAttendance::query()
            ->where('scheduled_lesson_id', $scheduled->id)
            ->where('user_id', $user->id)
            ->first();
    }

    private function respondAttendance(
        User $user,
        ScheduledLesson $scheduled,
        string $status,
    ): ScheduledLessonAttendance {
        $this->ensureUserCanAccess($user, $scheduled);

        if (! $user->isStudent()) {
            throw new InvalidArgumentException('Only students can confirm attendance.');
        }

        if (! $scheduled->isScheduled() || $scheduled->ends_at->isPast()) {
            throw new InvalidArgumentException('This lesson is no longer open for attendance.');
        }

        return ScheduledLessonAttendance::query()->updateOrCreate(
            [
                'scheduled_lesson_id' => $scheduled->id,
                'user_id' => $user->id,
            ],
            [
                'status' => $status,
                'responded_at' => now(),
            ]
        );
    }

    public function dispatchUpcomingReminders(): int
    {
        $lessons = ScheduledLesson::query()
            ->with('course')
            ->where('status', 'scheduled')
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [now(), now()->addHours(2)])
            ->get();

        $count = 0;

        foreach ($lessons as $lesson) {
            $this->notifyParticipants(
                $lesson,
                'lesson_upcoming',
                'Lesson coming up',
                "{$lesson->title} starts at {$lesson->starts_at->format('H:i')} — join the video call soon."
            );

            $lesson->update(['reminder_sent_at' => now()]);
            $count++;
        }

        return $count;
    }

    public function unreadNotificationCount(User $user): int
    {
        return AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * @return Collection<int, AppNotification>
     */
    public function recentNotifications(User $user, int $limit = 20): Collection
    {
        return AppNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function markNotificationRead(AppNotification $notification, User $user): void
    {
        abort_unless($notification->user_id === $user->id, 403);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }
    }

    public function markAllNotificationsRead(User $user): void
    {
        AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function notifyParticipants(
        ScheduledLesson $scheduled,
        string $type,
        string $title,
        string $body,
    ): void {
        $studentIds = $scheduled->participants()->pluck('users.id');

        if ($studentIds->isEmpty()) {
            $studentIds = Enrollment::query()
                ->where('course_id', $scheduled->course_id)
                ->where('status', 'active')
                ->pluck('student_id');
        }

        $userIds = $studentIds
            ->push($scheduled->teacher_id)
            ->unique()
            ->values();

        $link = route('education.schedule.show', $scheduled);
        $now = now();

        $rows = $userIds->map(fn (int $userId) => [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'data' => json_encode([
                'scheduled_lesson_id' => $scheduled->id,
                'course_id' => $scheduled->course_id,
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows !== []) {
            AppNotification::query()->insert($rows);
        }
    }
}
