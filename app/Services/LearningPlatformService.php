<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Message;
use App\Models\PlatformSetting;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class LearningPlatformService
{
    public function defaultSchool(): ?School
    {
        return School::query()->orderBy('id')->first();
    }

    public function createCourse(User $teacher, array $data): Course
    {
        $school = $this->defaultSchool();
        abort_unless($school, 404, 'No school configured.');

        return Course::create([
            'school_id' => $school->id,
            'teacher_id' => $teacher->isAdmin() && isset($data['teacher_id'])
                ? $data['teacher_id']
                : $teacher->id,
            'title' => $data['title'],
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'published',
        ]);
    }

    public function addLesson(Course $course, array $data): Lesson
    {
        $position = ($course->lessons()->max('position') ?? 0) + 1;

        return $course->lessons()->create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'position' => $data['position'] ?? $position,
            'is_published' => $data['is_published'] ?? true,
        ]);
    }

    public function updateLesson(Lesson $lesson, array $data): Lesson
    {
        $lesson->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'is_published' => $data['is_published'] ?? $lesson->is_published,
        ]);

        return $lesson->refresh();
    }

    public function deleteLesson(Lesson $lesson): void
    {
        $course = $lesson->course;
        $removedPosition = $lesson->position;

        DB::transaction(function () use ($lesson, $course, $removedPosition) {
            $lesson->progressRecords()->delete();
            $lesson->materials()->update(['lesson_id' => null]);
            $lesson->delete();

            $course->lessons()
                ->where('position', '>', $removedPosition)
                ->decrement('position');
        });
    }

    public function moveLesson(Lesson $lesson, string $direction): Lesson
    {
        $direction = strtolower($direction);
        abort_unless(in_array($direction, ['up', 'down'], true), 422, 'Invalid move direction.');

        return DB::transaction(function () use ($lesson, $direction) {
            $lesson = Lesson::query()->lockForUpdate()->findOrFail($lesson->id);

            $neighbor = $lesson->course->lessons()
                ->where('position', $direction === 'up' ? '<' : '>', $lesson->position)
                ->orderBy('position', $direction === 'up' ? 'desc' : 'asc')
                ->lockForUpdate()
                ->first();

            if ($neighbor === null) {
                return $lesson;
            }

            $currentPosition = $lesson->position;
            $lesson->update(['position' => $neighbor->position]);
            $neighbor->update(['position' => $currentPosition]);

            return $lesson->refresh();
        });
    }

    public function uploadMaterial(Course $course, User $uploader, UploadedFile $file, array $data): \App\Models\LearningMaterial
    {
        $path = $file->store('learning-materials/'.$course->id, 'local');

        return $course->materials()->create([
            'lesson_id' => $data['lesson_id'] ?? null,
            'uploaded_by' => $uploader->id,
            'title' => $data['title'] ?: $file->getClientOriginalName(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize() ?: 0,
        ]);
    }

    public function enrollStudent(Course $course, User $student): Enrollment
    {
        if (! $student->isStudent() && ! $student->isAdmin()) {
            throw new InvalidArgumentException('Only students can enroll in courses.');
        }

        return Enrollment::query()->updateOrCreate(
            ['course_id' => $course->id, 'student_id' => $student->id],
            ['status' => 'active', 'enrolled_at' => now()]
        );
    }

    public function createAssignment(Course $course, User $creator, array $data): Assignment
    {
        return $course->assignments()->create([
            'created_by' => $creator->id,
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?? null,
            'max_points' => $data['max_points'] ?? 100,
            'due_at' => $data['due_at'] ?? null,
            'is_published' => $data['is_published'] ?? true,
        ]);
    }

    public function submitHomework(Assignment $assignment, User $student, array $data, ?UploadedFile $file = null): AssignmentSubmission
    {
        if (! $student->isEnrolledIn($assignment->course)) {
            throw new InvalidArgumentException('You must be enrolled in this course to submit homework.');
        }

        $payload = [
            'content' => $data['content'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
            'score' => null,
            'feedback' => null,
            'graded_at' => null,
            'graded_by' => null,
        ];

        if ($file) {
            $payload['file_path'] = $file->store('submissions/'.$assignment->id, 'local');
            $payload['original_name'] = $file->getClientOriginalName();
        }

        return AssignmentSubmission::query()->updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            $payload
        );
    }

    public function gradeSubmission(AssignmentSubmission $submission, User $grader, array $data): AssignmentSubmission
    {
        $submission->update([
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'status' => 'graded',
            'graded_at' => now(),
            'graded_by' => $grader->id,
        ]);

        return $submission->fresh(['student', 'assignment']);
    }

    public function markLessonComplete(Lesson $lesson, User $student): LessonProgress
    {
        return LessonProgress::query()->updateOrCreate(
            ['lesson_id' => $lesson->id, 'student_id' => $student->id],
            ['completed' => true, 'completed_at' => now()]
        );
    }

    public function studentProgress(User $student, Course $course): array
    {
        $lessons = $course->lessons()
            ->where('is_published', true)
            ->orderBy('position')
            ->get(['id', 'title', 'position']);

        $completedLessonIds = LessonProgress::query()
            ->where('student_id', $student->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->where('completed', true)
            ->pluck('lesson_id')
            ->all();

        $assignments = $course->assignments()
            ->where('is_published', true)
            ->orderBy('due_at')
            ->orderBy('id')
            ->get(['id', 'title', 'max_points', 'due_at']);

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        $lessonItems = $lessons->map(function (Lesson $lesson) use ($completedLessonIds) {
            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'position' => $lesson->position,
                'completed' => in_array($lesson->id, $completedLessonIds, true),
            ];
        })->all();

        $assignmentItems = $assignments->map(function (Assignment $assignment) use ($submissions) {
            $submission = $submissions->get($assignment->id);
            $status = 'not_started';

            if ($submission !== null) {
                $status = $submission->status === 'graded' ? 'graded' : 'submitted';
            } elseif ($assignment->due_at && $assignment->due_at->isPast()) {
                $status = 'overdue';
            }

            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'max_points' => $assignment->max_points,
                'due_at' => $assignment->due_at,
                'status' => $status,
                'score' => $submission?->score,
                'feedback' => $submission?->feedback,
            ];
        })->all();

        $lessonsTotal = count($lessonItems);
        $lessonsCompleted = count(array_filter($lessonItems, fn (array $item) => $item['completed']));
        $assignmentsTotal = count($assignmentItems);
        $assignmentsSubmitted = count(array_filter(
            $assignmentItems,
            fn (array $item) => in_array($item['status'], ['submitted', 'graded'], true)
        ));
        $assignmentsGraded = collect($assignmentItems)->where('status', 'graded');

        return [
            'lessons_total' => $lessonsTotal,
            'lessons_completed' => $lessonsCompleted,
            'lesson_percent' => $lessonsTotal > 0
                ? round(($lessonsCompleted / $lessonsTotal) * 100, 1)
                : 0,
            'assignments_total' => $assignmentsTotal,
            'assignments_submitted' => $assignmentsSubmitted,
            'assignments_graded' => $assignmentsGraded->count(),
            'assignment_percent' => $assignmentsTotal > 0
                ? round(($assignmentsSubmitted / $assignmentsTotal) * 100, 1)
                : 0,
            'average_score' => $assignmentsGraded->avg('score'),
            'lessons' => $lessonItems,
            'assignments' => $assignmentItems,
        ];
    }

    /**
     * @return list<array{course: Course, progress: array}>
     */
    public function studentCourseSummaries(User $student): array
    {
        $enrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->with(['course.teacher'])
            ->latest('enrolled_at')
            ->get();

        $rows = [];

        foreach ($enrollments as $enrollment) {
            if ($enrollment->course === null) {
                continue;
            }

            $rows[] = [
                'course' => $enrollment->course,
                'progress' => $this->studentProgress($student, $enrollment->course),
            ];
        }

        return $rows;
    }

    public function coursePerformance(Course $course): array
    {
        $enrollments = $course->enrollments()->with('student')->get();
        $rows = [];

        foreach ($enrollments as $enrollment) {
            $rows[] = [
                'student' => $enrollment->student,
                'progress' => $this->studentProgress($enrollment->student, $course),
            ];
        }

        return $rows;
    }

    public function sendMessage(User $sender, User $recipient, array $data): Message
    {
        return Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'course_id' => $data['course_id'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);
    }

    public function adminReport(): array
    {
        return [
            'users' => User::query()->count(),
            'students' => User::query()->where('role', 'student')->count(),
            'teachers' => User::query()->where('role', 'teacher')->count(),
            'courses' => Course::query()->count(),
            'enrollments' => Enrollment::query()->count(),
            'submissions' => AssignmentSubmission::query()->count(),
            'graded_submissions' => AssignmentSubmission::query()->where('status', 'graded')->count(),
            'active_subscriptions' => Subscription::query()->where('status', 'active')->count(),
            'open_messages_unread' => Message::query()->whereNull('read_at')->count(),
        ];
    }

    public function updatePlatformSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            PlatformSetting::setValue($key, $value);
        }
    }

    public function ensureUserCanAccessCourse(User $user, Course $course): void
    {
        if ($user->isAdmin() || $course->teacher_id === $user->id) {
            return;
        }

        if ($user->isStudent() && $user->isEnrolledIn($course)) {
            return;
        }

        abort(403, 'You do not have access to this course.');
    }
}
