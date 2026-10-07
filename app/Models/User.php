<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'bio',
        'avatar_path',
        'password',
        'role',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function isCounselor(): bool
    {
        return $this->role === UserRole::Counselor;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isEducationStaff(): bool
    {
        return $this->role->isEducationStaff();
    }

    public function canImportRoster(): bool
    {
        return $this->role->canImportRoster();
    }

    public function canManageTickets(): bool
    {
        return $this->role->canManageTickets();
    }

    public function canAccessEducation(): bool
    {
        return $this->role->canAccessEducation();
    }

    public function canManageOwnProfile(): bool
    {
        return $this->role->canManageOwnProfile();
    }

    public function canEnrollInCourses(): bool
    {
        return $this->role->canEnrollInCourses();
    }

    public function canViewLessonsAndAssignments(): bool
    {
        return $this->role->canViewLessonsAndAssignments();
    }

    public function canSubmitHomework(): bool
    {
        return $this->role->canSubmitHomework();
    }

    public function canTrackLearningProgress(): bool
    {
        return $this->role->canTrackLearningProgress();
    }

    public function canMessageTeachers(): bool
    {
        return $this->role->canMessageTeachers();
    }

    public function canCreateCourses(): bool
    {
        return $this->role->canCreateCourses();
    }

    public function canUploadLearningMaterials(): bool
    {
        return $this->role->canUploadLearningMaterials();
    }

    public function canManageCourseStudents(): bool
    {
        return $this->role->canManageCourseStudents();
    }

    public function canCreateAssessments(): bool
    {
        return $this->role->canCreateAssessments();
    }

    public function canGradeAssignments(): bool
    {
        return $this->role->canGradeAssignments();
    }

    public function canMonitorStudentPerformance(): bool
    {
        return $this->role->canMonitorStudentPerformance();
    }

    public function canManageUsersAndPermissions(): bool
    {
        return $this->role->canManageUsersAndPermissions();
    }

    public function canConfigurePlatformSettings(): bool
    {
        return $this->role->canConfigurePlatformSettings();
    }

    public function canGenerateReports(): bool
    {
        return $this->role->canGenerateReports();
    }

    public function canManagePaymentsAndSubscriptions(): bool
    {
        return $this->role->canManagePaymentsAndSubscriptions();
    }

    public function canMaintainSecurity(): bool
    {
        return $this->role->canMaintainSecurity();
    }

    public function hasRole(UserRole|string $role): bool
    {
        $role = $role instanceof UserRole ? $role : UserRole::from($role);

        return $this->role === $role;
    }

    public function isEnrolledIn(Course $course): bool
    {
        return $this->enrollments()->where('course_id', $course->id)->where('status', 'active')->exists();
    }
}
