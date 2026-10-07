<?php

namespace Tests\Feature\Education;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\School;
use App\Models\Student;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EducationSupportTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $admin;

    private User $teacher;

    private User $studentUser;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->teacher = User::factory()->teacher()->create();
        $this->studentUser = User::factory()->student()->create();

        $this->school = School::factory()->create([
            'name' => 'Test Academy',
            'slug' => 'test-academy',
        ]);

        $this->student = Student::factory()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->studentUser->id,
            'external_id' => 'S1001',
            'name' => 'Alex Student',
        ]);
    }

    public function test_guests_are_redirected_from_education_dashboard(): void
    {
        $this->get(route('education.dashboard'))->assertRedirect(route('login'));
    }

    public function test_staff_can_view_education_dashboard(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('education.dashboard'))
            ->assertOk()
            ->assertSee('Education support')
            ->assertSee('Test Academy');
    }

    public function test_staff_can_list_and_view_students(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('education.students.index'))
            ->assertOk()
            ->assertSee('Alex Student');

        $this->actingAs($this->teacher)
            ->get(route('education.students.show', $this->student))
            ->assertOk()
            ->assertSee('S1001');
    }

    public function test_students_cannot_browse_student_directory(): void
    {
        $this->actingAs($this->studentUser)
            ->get(route('education.students.index'))
            ->assertForbidden();
    }

    public function test_student_can_create_ticket_for_self(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('education.tickets.store'), [
            'student_id' => $this->student->id,
            'subject' => 'Need tutoring',
            'body' => 'I need help with algebra.',
            'priority' => 'high',
        ]);

        $ticket = SupportTicket::query()->first();

        $response->assertRedirect(route('education.tickets.show', $ticket));
        $this->assertSame('Need tutoring', $ticket->subject);
        $this->assertSame(TicketStatus::Open, $ticket->status);
    }

    public function test_student_cannot_create_ticket_for_another_student(): void
    {
        $other = Student::factory()->create(['school_id' => $this->school->id]);

        $this->actingAs($this->studentUser)->post(route('education.tickets.store'), [
            'student_id' => $other->id,
            'subject' => 'Not allowed',
            'body' => 'Should fail',
            'priority' => 'normal',
        ])->assertForbidden();
    }

    public function test_teacher_can_update_ticket_status(): void
    {
        $ticket = SupportTicket::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'created_by' => $this->teacher->id,
            'status' => TicketStatus::Open,
        ]);

        $this->actingAs($this->teacher)->put(route('education.tickets.update', $ticket), [
            'status' => TicketStatus::Resolved->value,
            'assigned_to' => $this->teacher->id,
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Resolved, $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_admin_can_import_roster_csv(): void
    {
        $file = new UploadedFile(
            base_path('tests/fixtures/students_roster_sample.csv'),
            'students_roster_sample.csv',
            'text/csv',
            null,
            true,
        );

        $this->actingAs($this->admin)
            ->post(route('education.roster.store'), ['file' => $file])
            ->assertRedirect(route('education.students.index'));

        $this->assertDatabaseHas('students', [
            'school_id' => $this->school->id,
            'external_id' => 'S2001',
            'name' => 'Casey Morgan',
        ]);
    }

    public function test_student_cannot_import_roster(): void
    {
        $file = new UploadedFile(
            base_path('tests/fixtures/students_roster_sample.csv'),
            'students_roster_sample.csv',
            'text/csv',
            null,
            true,
        );

        $this->actingAs($this->studentUser)
            ->post(route('education.roster.store'), ['file' => $file])
            ->assertForbidden();
    }

    public function test_home_redirects_education_users_to_education_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get('/')
            ->assertRedirect(route('education.dashboard'));
    }

    public function test_registration_creates_student_role(): void
    {
        $this->post(route('register'), [
            'name' => 'New Learner',
            'email' => 'learner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('education.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'learner@example.com',
            'role' => UserRole::Student->value,
        ]);
    }
}
