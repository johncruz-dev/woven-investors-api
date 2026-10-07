<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Counselor = 'counselor';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Teacher => 'Teacher / Instructor',
            self::Counselor => 'Counselor',
            self::Student => 'Student',
        };
    }

    public function isEducationStaff(): bool
    {
        return match ($this) {
            self::Admin, self::Teacher, self::Counselor => true,
            default => false,
        };
    }

    public function canImportRoster(): bool
    {
        return match ($this) {
            self::Admin, self::Teacher => true,
            default => false,
        };
    }

    public function canManageTickets(): bool
    {
        return $this->isEducationStaff();
    }

    public function canAccessEducation(): bool
    {
        return true;
    }

    // --- Student capabilities ---

    public function canManageOwnProfile(): bool
    {
        return true;
    }

    public function canEnrollInCourses(): bool
    {
        return $this === self::Student || $this === self::Admin;
    }

    public function canViewLessonsAndAssignments(): bool
    {
        return match ($this) {
            self::Admin, self::Teacher, self::Student => true,
            default => false,
        };
    }

    public function canSubmitHomework(): bool
    {
        return $this === self::Student;
    }

    public function canTrackLearningProgress(): bool
    {
        return $this === self::Student || $this === self::Admin;
    }

    public function canMessageTeachers(): bool
    {
        return match ($this) {
            self::Student, self::Teacher, self::Admin => true,
            default => false,
        };
    }

    // --- Teacher / Instructor capabilities ---

    public function canCreateCourses(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }

    public function canUploadLearningMaterials(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }

    public function canManageCourseStudents(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }

    public function canCreateAssessments(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }

    public function canGradeAssignments(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }

    public function canMonitorStudentPerformance(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }

    // --- Administrator capabilities ---

    public function canManageUsersAndPermissions(): bool
    {
        return $this === self::Admin;
    }

    public function canConfigurePlatformSettings(): bool
    {
        return $this === self::Admin;
    }

    public function canGenerateReports(): bool
    {
        return $this === self::Admin;
    }

    public function canManagePaymentsAndSubscriptions(): bool
    {
        return $this === self::Admin;
    }

    public function canMaintainSecurity(): bool
    {
        return $this === self::Admin;
    }
}
