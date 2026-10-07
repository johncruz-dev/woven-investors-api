<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->canCreateCourses() && ! $user->isStudent()) {
            $courses = $user->isAdmin()
                ? Course::query()->with('teacher')->withCount('enrollments')->latest()->paginate(15)
                : Course::query()->where('teacher_id', $user->id)->withCount('enrollments')->latest()->paginate(15);
        } else {
            $courses = Course::query()
                ->where('status', 'published')
                ->with('teacher')
                ->withCount('enrollments')
                ->latest()
                ->paginate(15);
        }

        return view('education.courses.index', [
            'user' => $user,
            'courses' => $courses,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->canCreateCourses(), 403);

        return view('education.courses.create', [
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canCreateCourses(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $course = $this->learningPlatformService->createCourse($request->user(), $validated);

        return redirect()
            ->route('education.courses.show', $course)
            ->with('status', 'Course created.');
    }

    public function show(Request $request, Course $course): View
    {
        $user = $request->user();
        $isOwner = $user->isAdmin() || $course->teacher_id === $user->id;
        $isEnrolled = $user->isEnrolledIn($course);

        if (! $isOwner && ! $isEnrolled && ! ($user->canEnrollInCourses() && $course->isPublished())) {
            abort(403, 'You do not have access to this course.');
        }

        $course->load(['teacher', 'materials', 'enrollments.student']);
        $course->setRelation(
            'lessons',
            $course->lessons()
                ->when(! $isOwner, fn ($query) => $query->where('is_published', true))
                ->orderBy('position')
                ->get()
        );
        $course->setRelation(
            'assignments',
            $course->assignments()
                ->when(! $isOwner, fn ($query) => $query->where('is_published', true))
                ->orderBy('due_at')
                ->orderBy('id')
                ->get()
        );

        $progress = ($user->isStudent() && $isEnrolled)
            ? $this->learningPlatformService->studentProgress($user, $course)
            : null;

        $completedLessonIds = $progress
            ? collect($progress['lessons'])->where('completed', true)->pluck('id')->all()
            : [];

        $assignmentStatusById = $progress
            ? collect($progress['assignments'])->keyBy('id')
            : collect();

        $performance = $user->canMonitorStudentPerformance() && $isOwner
            ? $this->learningPlatformService->coursePerformance($course)
            : [];

        return view('education.courses.show', [
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'completedLessonIds' => $completedLessonIds,
            'assignmentStatusById' => $assignmentStatusById,
            'performance' => $performance,
            'isEnrolled' => $isEnrolled,
            'isOwner' => $isOwner,
        ]);
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->canEnrollInCourses(), 403);
        abort_unless($course->isPublished(), 404);

        $this->learningPlatformService->enrollStudent($course, $request->user());

        return back()->with('status', 'Enrolled in course.');
    }

    public function manageStudents(Request $request, Course $course): View
    {
        abort_unless($request->user()->canManageCourseStudents(), 403);
        abort_unless($request->user()->isAdmin() || $course->teacher_id === $request->user()->id, 403);

        $course->load('enrollments.student');
        $students = User::query()->where('role', 'student')->where('is_active', true)->orderBy('name')->get();

        return view('education.courses.students', [
            'user' => $request->user(),
            'course' => $course,
            'students' => $students,
        ]);
    }

    public function addStudent(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->canManageCourseStudents(), 403);
        abort_unless($request->user()->isAdmin() || $course->teacher_id === $request->user()->id, 403);

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::query()->findOrFail($validated['student_id']);
        abort_unless($student->isStudent(), 422);

        $this->learningPlatformService->enrollStudent($course, $student);

        return back()->with('status', 'Student enrolled.');
    }

    public function removeStudent(Request $request, Course $course, User $student): RedirectResponse
    {
        abort_unless($request->user()->canManageCourseStudents(), 403);
        abort_unless($request->user()->isAdmin() || $course->teacher_id === $request->user()->id, 403);

        Enrollment::query()
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->delete();

        return back()->with('status', 'Student removed from course.');
    }
}
