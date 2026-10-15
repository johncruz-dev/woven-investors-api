<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\PlatformSetting;
use App\Models\ScheduledLesson;
use App\Models\ScheduledLessonAttendance;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => 'password', 'role' => UserRole::Admin, 'is_active' => true]
        );

        $teacher = User::query()->firstOrCreate(
            ['email' => 'teacher@example.com'],
            ['name' => 'Teacher User', 'password' => 'password', 'role' => UserRole::Teacher, 'is_active' => true]
        );

        $counselor = User::query()->firstOrCreate(
            ['email' => 'counselor@example.com'],
            ['name' => 'Counselor User', 'password' => 'password', 'role' => UserRole::Counselor, 'is_active' => true]
        );

        $studentUser = User::query()->firstOrCreate(
            ['email' => 'student@example.com'],
            ['name' => 'Alex Student', 'password' => 'password', 'role' => UserRole::Student, 'is_active' => true, 'bio' => 'Year 11 student']
        );

        $jordanUser = User::query()->firstOrCreate(
            ['email' => 'jordan@example.com'],
            ['name' => 'Jordan Lee', 'password' => 'password', 'role' => UserRole::Student, 'is_active' => true, 'bio' => 'Year 10 student']
        );

        $samUser = User::query()->firstOrCreate(
            ['email' => 'sam@example.com'],
            ['name' => 'Sam Rivera', 'password' => 'password', 'role' => UserRole::Student, 'is_active' => true, 'bio' => 'Year 12 student']
        );

        $school = School::query()->firstOrCreate(
            ['slug' => 'woven-academy'],
            ['name' => 'Woven Academy']
        );

        $alex = Student::query()->firstOrCreate(
            ['school_id' => $school->id, 'external_id' => 'S1001'],
            [
                'user_id' => $studentUser->id,
                'name' => 'Alex Student',
                'email' => 'student@example.com',
                'grade_level' => '11',
                'status' => 'active',
            ]
        );

        $jordan = Student::query()->firstOrCreate(
            ['school_id' => $school->id, 'external_id' => 'S1002'],
            [
                'user_id' => $jordanUser->id,
                'name' => 'Jordan Lee',
                'email' => 'jordan@example.com',
                'grade_level' => '10',
                'status' => 'active',
            ]
        );
        $jordan->forceFill(['user_id' => $jordanUser->id])->save();

        $sam = Student::query()->firstOrCreate(
            ['school_id' => $school->id, 'external_id' => 'S1003'],
            [
                'user_id' => $samUser->id,
                'name' => 'Sam Rivera',
                'email' => 'sam@example.com',
                'grade_level' => '12',
                'status' => 'active',
            ]
        );
        $sam->forceFill(['user_id' => $samUser->id])->save();

        SupportTicket::query()->firstOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $alex->id,
                'subject' => 'Need help with exam stress',
            ],
            [
                'created_by' => $studentUser->id,
                'assigned_to' => $counselor->id,
                'body' => 'I have three exams next week and I am feeling overwhelmed. Can we talk?',
                'status' => TicketStatus::Open,
                'priority' => TicketPriority::High,
            ]
        );

        SupportTicket::query()->firstOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $jordan->id,
                'subject' => 'Attendance follow-up',
            ],
            [
                'created_by' => $teacher->id,
                'assigned_to' => $teacher->id,
                'body' => 'Jordan has missed several morning classes. Please check in.',
                'status' => TicketStatus::InProgress,
                'priority' => TicketPriority::Normal,
            ]
        );

        $course = Course::query()->firstOrCreate(
            ['school_id' => $school->id, 'code' => 'ALG-101'],
            [
                'teacher_id' => $teacher->id,
                'title' => 'Algebra Foundations',
                'description' => 'Core algebra skills for secondary students.',
                'status' => 'published',
            ]
        );

        $linearLesson = Lesson::query()->firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Linear equations'],
            [
                'content' => 'Learn how to solve linear equations step by step.',
                'position' => 1,
                'is_published' => true,
            ]
        );

        $quadraticLesson = Lesson::query()->firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Quadratic equations'],
            [
                'content' => 'Introduction to factoring and the quadratic formula.',
                'position' => 2,
                'is_published' => true,
            ]
        );

        Assignment::query()->firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Homework 1'],
            [
                'created_by' => $teacher->id,
                'instructions' => 'Solve the attached linear equation problems and explain your steps.',
                'max_points' => 100,
                'is_published' => true,
            ]
        );

        foreach ([$studentUser, $jordanUser, $samUser] as $enrolledStudent) {
            Enrollment::query()->firstOrCreate(
                ['course_id' => $course->id, 'student_id' => $enrolledStudent->id],
                ['status' => 'active', 'enrolled_at' => now()]
            );
        }

        Subscription::query()->firstOrCreate(
            ['user_id' => $studentUser->id, 'plan' => 'standard'],
            [
                'status' => 'active',
                'amount' => 29.00,
                'currency' => 'GBP',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
            ]
        );

        $tomorrowLive = now()->addDay()->setTime(10, 0);
        $liveWorkshop = ScheduledLesson::query()->firstOrCreate(
            [
                'course_id' => $course->id,
                'title' => 'Live video: Linear equations workshop',
            ],
            [
                'teacher_id' => $teacher->id,
                'lesson_id' => $linearLesson->id,
                'description' => 'Join the video call after reading Linear equations. Bring 2 questions.',
                'starts_at' => $tomorrowLive,
                'ends_at' => $tomorrowLive->copy()->addHour(),
                'meeting_url' => 'https://meet.google.com/woven-algebra-live',
                'status' => 'scheduled',
            ]
        );
        $liveWorkshop->forceFill([
            'teacher_id' => $teacher->id,
            'lesson_id' => $linearLesson->id,
            'starts_at' => $tomorrowLive,
            'ends_at' => $tomorrowLive->copy()->addHour(),
            'meeting_url' => 'https://meet.google.com/woven-algebra-live',
            'status' => 'scheduled',
        ])->save();

        $nextWeek = now()->addDays(3)->setTime(14, 0);
        $quadraticCall = ScheduledLesson::query()->firstOrCreate(
            [
                'course_id' => $course->id,
                'title' => 'Video call: Quadratics Q&A',
            ],
            [
                'teacher_id' => $teacher->id,
                'lesson_id' => $quadraticLesson->id,
                'description' => 'Office-hours style video call for quadratic practice.',
                'starts_at' => $nextWeek,
                'ends_at' => $nextWeek->copy()->addMinutes(45),
                'meeting_url' => 'https://meet.google.com/woven-quadratics-qa',
                'status' => 'scheduled',
            ]
        );
        $quadraticCall->forceFill([
            'teacher_id' => $teacher->id,
            'lesson_id' => $quadraticLesson->id,
            'starts_at' => $nextWeek,
            'ends_at' => $nextWeek->copy()->addMinutes(45),
            'meeting_url' => 'https://meet.google.com/woven-quadratics-qa',
            'status' => 'scheduled',
        ])->save();

        $soon = now()->addHour()->seconds(0);
        $comingUp = ScheduledLesson::query()->firstOrCreate(
            [
                'course_id' => $course->id,
                'title' => 'Coming up: Quick algebra check-in',
            ],
            [
                'teacher_id' => $teacher->id,
                'lesson_id' => $linearLesson->id,
                'description' => 'Short check-in call. Confirm attendance if you can join.',
                'starts_at' => $soon,
                'ends_at' => $soon->copy()->addMinutes(30),
                'meeting_url' => 'https://meet.google.com/woven-checkin',
                'status' => 'scheduled',
            ]
        );
        $comingUp->forceFill([
            'teacher_id' => $teacher->id,
            'lesson_id' => $linearLesson->id,
            'starts_at' => $soon,
            'ends_at' => $soon->copy()->addMinutes(30),
            'meeting_url' => 'https://meet.google.com/woven-checkin',
            'status' => 'scheduled',
            'reminder_sent_at' => null,
        ])->save();

        $liveWorkshop->participants()->syncWithoutDetaching([$studentUser->id, $jordanUser->id]);
        $quadraticCall->participants()->syncWithoutDetaching([$studentUser->id, $samUser->id]);
        $comingUp->participants()->syncWithoutDetaching([$studentUser->id, $jordanUser->id, $samUser->id]);

        ScheduledLessonAttendance::query()->firstOrCreate(
            ['scheduled_lesson_id' => $liveWorkshop->id, 'user_id' => $jordanUser->id],
            ['status' => 'confirmed', 'responded_at' => now()]
        );

        PlatformSetting::setValue('platform_name', 'Woven Education');
        PlatformSetting::setValue('support_email', 'support@woven.example');
        PlatformSetting::setValue('allow_registration', '1');
        PlatformSetting::setValue('maintenance_mode', '0');

        unset($admin, $sam);
    }
}
