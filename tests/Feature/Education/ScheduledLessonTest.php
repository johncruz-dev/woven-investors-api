<?php

namespace Tests\Feature\Education;

use App\Models\AppNotification;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\ScheduledLesson;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledLessonTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $student;

    private Course $course;

    private Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->teacher()->create();
        $this->student = User::factory()->student()->create();
        $school = School::factory()->create();

        $this->course = Course::query()->create([
            'school_id' => $school->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Algebra Live',
            'code' => 'ALG-1',
            'status' => 'published',
        ]);

        $this->lesson = Lesson::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Chapter 1 reading',
            'content' => 'Read pages 1-10',
            'position' => 1,
            'is_published' => true,
        ]);

        Enrollment::query()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
    }

    public function test_teacher_can_schedule_video_lesson_and_student_is_notified(): void
    {
        $starts = now()->addDay()->setTime(10, 0);
        $ends = $starts->copy()->addHour();

        $otherStudent = User::factory()->student()->create();

        $this->actingAs($this->teacher)
            ->post(route('education.schedule.store'), [
                'course_id' => $this->course->id,
                'lesson_id' => $this->lesson->id,
                'title' => 'Live video workshop',
                'description' => 'Bring questions',
                'starts_at' => $starts->format('Y-m-d\TH:i'),
                'ends_at' => $ends->format('Y-m-d\TH:i'),
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
                'student_ids' => [$this->student->id],
            ])
            ->assertRedirect();

        $scheduled = ScheduledLesson::query()->first();
        $this->assertNotNull($scheduled);
        $this->assertSame('Live video workshop', $scheduled->title);
        $this->assertTrue($scheduled->participants->contains('id', $this->student->id));
        $this->assertFalse($scheduled->participants->contains('id', $otherStudent->id));

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'lesson_scheduled',
            'title' => 'Video lesson scheduled',
        ]);

        $this->actingAs($otherStudent)
            ->get(route('education.schedule.show', $scheduled))
            ->assertForbidden();

        $this->actingAs($this->student)
            ->get(route('education.schedule.index'))
            ->assertOk()
            ->assertSee('Live video workshop');

        $this->actingAs($this->student)
            ->get(route('education.schedule.show', $scheduled))
            ->assertOk()
            ->assertSee('Join')
            ->assertSee('Chapter 1 reading')
            ->assertSee('Reading materials');
    }

    public function test_upcoming_lesson_creates_coming_up_notification(): void
    {
        $scheduled = ScheduledLesson::query()->create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher->id,
            'lesson_id' => $this->lesson->id,
            'title' => 'Starting soon class',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'meeting_url' => 'https://zoom.us/j/123456789',
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->student)
            ->get(route('education.dashboard'))
            ->assertOk();

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'lesson_upcoming',
            'title' => 'Lesson coming up',
        ]);

        $this->assertNotNull($scheduled->fresh()->reminder_sent_at);

        $notification = AppNotification::query()
            ->where('user_id', $this->student->id)
            ->where('type', 'lesson_upcoming')
            ->first();

        $this->actingAs($this->student)
            ->get(route('education.notifications.index'))
            ->assertOk()
            ->assertSee('Lesson coming up')
            ->assertSee('nav-badge');

        $this->actingAs($this->student)
            ->get(route('education.notifications.read', $notification))
            ->assertRedirect(route('education.schedule.show', $scheduled));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_student_cannot_schedule_lessons(): void
    {
        $this->actingAs($this->student)
            ->post(route('education.schedule.store'), [
                'course_id' => $this->course->id,
                'title' => 'Unauthorized',
                'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addDay()->addHour()->format('Y-m-d\TH:i'),
                'meeting_url' => 'https://meet.google.com/x',
            ])
            ->assertForbidden();
    }

    public function test_student_can_confirm_attendance_for_video_call(): void
    {
        $scheduled = ScheduledLesson::query()->create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher->id,
            'lesson_id' => $this->lesson->id,
            'title' => 'Confirmable workshop',
            'starts_at' => now()->addDays(2)->setTime(11, 0),
            'ends_at' => now()->addDays(2)->setTime(12, 0),
            'meeting_url' => 'https://meet.google.com/confirm-demo',
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->student)
            ->post(route('education.schedule.confirm', $scheduled))
            ->assertRedirect();

        $this->assertDatabaseHas('scheduled_lesson_attendances', [
            'scheduled_lesson_id' => $scheduled->id,
            'user_id' => $this->student->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($this->student)
            ->get(route('education.schedule.show', $scheduled))
            ->assertOk()
            ->assertSee('Attending')
            ->assertSee('Confirm I’ll attend');
    }
}
