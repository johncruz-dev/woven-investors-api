<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_permissions(): void
    {
        $role = UserRole::Admin;

        $this->assertSame('admin', $role->value);
        $this->assertSame('Administrator', $role->label());
        $this->assertTrue($role->isEducationStaff());
        $this->assertTrue($role->canAccessEducation());
        $this->assertTrue($role->canManageUsersAndPermissions());
        $this->assertTrue($role->canConfigurePlatformSettings());
        $this->assertTrue($role->canGenerateReports());
        $this->assertTrue($role->canManagePaymentsAndSubscriptions());
        $this->assertTrue($role->canMaintainSecurity());
        $this->assertTrue($role->canCreateCourses());
    }

    public function test_teacher_and_student_education_permissions(): void
    {
        $teacher = UserRole::Teacher;
        $this->assertSame('Teacher / Instructor', $teacher->label());
        $this->assertTrue($teacher->isEducationStaff());
        $this->assertTrue($teacher->canImportRoster());
        $this->assertTrue($teacher->canCreateCourses());
        $this->assertTrue($teacher->canUploadLearningMaterials());
        $this->assertTrue($teacher->canManageCourseStudents());
        $this->assertTrue($teacher->canCreateAssessments());
        $this->assertTrue($teacher->canGradeAssignments());
        $this->assertTrue($teacher->canMonitorStudentPerformance());
        $this->assertFalse($teacher->canManageUsersAndPermissions());

        $this->assertTrue(UserRole::Counselor->canManageTickets());
        $this->assertFalse(UserRole::Counselor->canImportRoster());

        $student = UserRole::Student;
        $this->assertFalse($student->isEducationStaff());
        $this->assertTrue($student->canAccessEducation());
        $this->assertTrue($student->canManageOwnProfile());
        $this->assertTrue($student->canEnrollInCourses());
        $this->assertTrue($student->canViewLessonsAndAssignments());
        $this->assertTrue($student->canSubmitHomework());
        $this->assertTrue($student->canTrackLearningProgress());
        $this->assertTrue($student->canMessageTeachers());
        $this->assertFalse($student->canImportRoster());
        $this->assertFalse($student->canCreateCourses());
    }

    public function test_user_admin_helpers(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isStudent());
        $this->assertTrue($user->hasRole(UserRole::Admin));
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('student'));
    }

    public function test_factory_defaults_to_student(): void
    {
        $user = User::factory()->create();

        $this->assertSame(UserRole::Student, $user->role);
    }
}
