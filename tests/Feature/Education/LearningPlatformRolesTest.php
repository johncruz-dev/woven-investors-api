<?php

namespace Tests\Feature\Education;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LearningPlatformRolesTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $admin;

    private User $teacher;

    private User $student;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->teacher = User::factory()->teacher()->create();
        $this->student = User::factory()->student()->create();
        $this->school = School::factory()->create();

        $this->course = Course::query()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Biology 101',
            'code' => 'BIO-101',
            'description' => 'Intro biology',
            'status' => 'published',
        ]);
    }

    public function test_student_can_manage_profile_enroll_submit_and_message(): void
    {
        $this->actingAs($this->student)
            ->put(route('education.profile.update'), [
                'name' => 'Alex Updated',
                'phone' => '07000000000',
                'bio' => 'Learner bio',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'Alex Updated',
            'phone' => '07000000000',
        ]);

        $this->actingAs($this->student)
            ->post(route('education.courses.enroll', $this->course))
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Cells',
            'content' => 'Cell basics',
            'position' => 1,
            'is_published' => true,
        ]);

        $this->actingAs($this->student)
            ->post(route('education.lessons.complete', [$this->course, $lesson]))
            ->assertRedirect();

        $assignment = Assignment::query()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->teacher->id,
            'title' => 'Lab report',
            'instructions' => 'Write a short report',
            'max_points' => 50,
            'is_published' => true,
        ]);

        $this->actingAs($this->student)
            ->post(route('education.assignments.submit', $assignment), [
                'content' => 'My homework answer',
            ])
            ->assertRedirect();

        $this->actingAs($this->student)
            ->get(route('education.courses.show', $this->course))
            ->assertOk()
            ->assertSee('Lesson progress')
            ->assertSee('Completed')
            ->assertSee('Submitted')
            ->assertSee('Lab report');

        $this->actingAs($this->student)
            ->get(route('education.dashboard'))
            ->assertOk()
            ->assertSee('My learning')
            ->assertSee('Course progress')
            ->assertSee($this->course->title)
            ->assertSee('100% lessons');

        $this->actingAs($this->student)
            ->post(route('education.messages.store'), [
                'recipient_id' => $this->teacher->id,
                'subject' => 'Question',
                'body' => 'Can you help with lesson 1?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->student->id,
            'recipient_id' => $this->teacher->id,
            'subject' => 'Question',
        ]);
    }

    public function test_teacher_can_create_course_lesson_assessment_and_grade(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->teacher)->post(route('education.courses.store'), [
            'title' => 'Chemistry',
            'code' => 'CHEM-101',
            'description' => 'Intro chem',
        ]);

        $course = Course::query()->where('code', 'CHEM-101')->first();
        $response->assertRedirect(route('education.courses.show', $course));

        $this->actingAs($this->teacher)->post(route('education.lessons.store', $course), [
            'title' => 'Atoms',
            'content' => 'Atomic structure',
        ])->assertRedirect();

        $this->actingAs($this->teacher)->post(route('education.materials.store', $course), [
            'title' => 'Slides',
            'file' => UploadedFile::fake()->create('slides.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->actingAs($this->teacher)->post(route('education.assignments.store', $course), [
            'title' => 'Quiz 1',
            'instructions' => 'Answer all questions',
            'max_points' => 20,
        ])->assertRedirect();

        Enrollment::query()->create([
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $assignment = Assignment::query()->where('course_id', $course->id)->first();

        $this->actingAs($this->student)->post(route('education.assignments.submit', $assignment), [
            'content' => 'Answers here',
        ]);

        $submission = $assignment->submissions()->first();

        $this->actingAs($this->teacher)->post(route('education.submissions.grade', $submission), [
            'score' => 18,
            'feedback' => 'Great work',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'score' => 18,
            'status' => 'graded',
        ]);

        $this->actingAs($this->teacher)
            ->get(route('education.courses.show', $course))
            ->assertOk()
            ->assertSee('Student performance');
    }

    public function test_admin_can_manage_users_settings_reports_subscriptions_and_security(): void
    {
        $this->actingAs($this->admin)
            ->post(route('education.admin.users.store'), [
                'name' => 'New Teacher',
                'email' => 'newteacher@example.com',
                'password' => 'password',
                'role' => UserRole::Teacher->value,
            ])
            ->assertRedirect();

        $managed = User::query()->where('email', 'newteacher@example.com')->first();

        $this->actingAs($this->admin)
            ->put(route('education.admin.users.update', $managed), [
                'role' => UserRole::Student->value,
                'is_active' => 0,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'newteacher@example.com',
            'role' => UserRole::Student->value,
            'is_active' => 0,
        ]);

        $this->actingAs($this->admin)
            ->put(route('education.admin.settings.update'), [
                'platform_name' => 'Woven Learn',
                'support_email' => 'help@example.com',
                'allow_registration' => '0',
                'maintenance_mode' => '0',
            ])
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('education.admin.reports.index'))
            ->assertOk()
            ->assertSee('users');

        $this->actingAs($this->admin)
            ->post(route('education.admin.subscriptions.store'), [
                'user_id' => $this->student->id,
                'plan' => 'premium',
                'amount' => 49.99,
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->student->id,
            'plan' => 'premium',
        ]);

        $this->actingAs($this->admin)
            ->get(route('education.admin.security.index'))
            ->assertOk()
            ->assertSee('Maintain security');
    }

    public function test_teacher_can_edit_reorder_and_delete_lessons(): void
    {
        $first = Lesson::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Lesson A',
            'content' => 'First',
            'position' => 1,
            'is_published' => true,
        ]);

        $second = Lesson::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Lesson B',
            'content' => 'Second',
            'position' => 2,
            'is_published' => true,
        ]);

        $this->actingAs($this->teacher)
            ->get(route('education.lessons.edit', [$this->course, $first]))
            ->assertOk()
            ->assertSee('Edit lesson');

        $this->actingAs($this->teacher)
            ->put(route('education.lessons.update', [$this->course, $first]), [
                'title' => 'Lesson A revised',
                'content' => 'Updated content',
                'is_published' => '0',
            ])
            ->assertRedirect(route('education.lessons.show', [$this->course, $first]));

        $this->assertDatabaseHas('lessons', [
            'id' => $first->id,
            'title' => 'Lesson A revised',
            'content' => 'Updated content',
            'is_published' => 0,
        ]);

        Enrollment::query()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $this->actingAs($this->student)
            ->get(route('education.lessons.show', [$this->course, $first]))
            ->assertNotFound();

        $this->actingAs($this->student)
            ->get(route('education.courses.show', $this->course))
            ->assertOk()
            ->assertDontSee('Lesson A revised')
            ->assertSee('Lesson B');

        $this->actingAs($this->teacher)
            ->post(route('education.lessons.move', [$this->course, $second]), [
                'direction' => 'up',
            ])
            ->assertRedirect();

        $this->assertSame(1, $second->fresh()->position);
        $this->assertSame(2, $first->fresh()->position);

        $this->actingAs($this->teacher)
            ->delete(route('education.lessons.destroy', [$this->course, $first]))
            ->assertRedirect(route('education.courses.show', $this->course));

        $this->assertDatabaseMissing('lessons', ['id' => $first->id]);
        $this->assertSame(1, $second->fresh()->position);

        $this->actingAs($this->student)
            ->put(route('education.lessons.update', [$this->course, $second]), [
                'title' => 'Nope',
                'content' => 'Nope',
            ])
            ->assertForbidden();
    }

    public function test_student_cannot_create_courses_or_access_admin(): void
    {
        $this->actingAs($this->student)
            ->post(route('education.courses.store'), [
                'title' => 'Hacked',
                'code' => 'X',
            ])
            ->assertForbidden();

        $this->actingAs($this->student)
            ->get(route('education.admin.users.index'))
            ->assertForbidden();
    }
}
