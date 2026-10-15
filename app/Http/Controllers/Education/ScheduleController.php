<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ScheduledLesson;
use App\Services\ScheduledLessonService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduledLessonService $scheduledLessonService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->canViewLessonsAndAssignments(), 403);

        $month = $request->integer('month', (int) now()->month);
        $year = $request->integer('year', (int) now()->year);

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        $cursor = Carbon::create($year, $month, 1)->startOfDay();
        $from = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $to = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $lessons = $this->scheduledLessonService->forUserInRange($request->user(), $from, $to);
        $byDate = $lessons->groupBy(fn (ScheduledLesson $lesson) => $lesson->starts_at->toDateString());

        $days = collect();
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $key = $day->toDateString();
            $days->push([
                'date' => $day->copy(),
                'in_month' => $day->month === $month,
                'is_today' => $day->isToday(),
                'lessons' => $byDate->get($key, collect()),
            ]);
        }

        $courses = Course::query()
            ->when(
                $request->user()->isTeacher() && ! $request->user()->isAdmin(),
                fn ($query) => $query->where('teacher_id', $request->user()->id),
            )
            ->when(
                $request->user()->isStudent(),
                fn ($query) => $query->whereIn(
                    'id',
                    $request->user()->enrollments()->where('status', 'active')->pluck('course_id'),
                ),
            )
            ->with([
                'lessons' => fn ($query) => $query->where('is_published', true)->orderBy('position'),
                'enrollments' => fn ($query) => $query->where('status', 'active')->with('student:id,name,email'),
            ])
            ->orderBy('title')
            ->get(['id', 'title', 'teacher_id']);

        return view('education.schedule.index', [
            'user' => $request->user(),
            'cursor' => $cursor,
            'days' => $days,
            'upcoming' => $this->scheduledLessonService->upcomingForUser($request->user()),
            'courses' => $courses,
            'prev' => $cursor->copy()->subMonth(),
            'next' => $cursor->copy()->addMonth(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canCreateCourses(), 403);

        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'meeting_url' => ['required', 'url', 'max:500'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $course = Course::query()->findOrFail($validated['course_id']);

        try {
            $scheduled = $this->scheduledLessonService->schedule($request->user(), $course, $validated);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['student_ids' => $e->getMessage()]);
        }

        $count = count($validated['student_ids']);

        return redirect()
            ->route('education.schedule.show', $scheduled)
            ->with('status', "Video lesson scheduled for {$count} selected student(s).");
    }

    public function show(Request $request, ScheduledLesson $scheduledLesson): View
    {
        abort_unless($request->user()->canViewLessonsAndAssignments(), 403);

        $scheduledLesson->load([
            'course.materials',
            'teacher',
            'lesson.materials',
            'participants:id,name,email',
            'attendances.user:id,name,email',
        ]);
        $this->scheduledLessonService->ensureUserCanAccess($request->user(), $scheduledLesson);

        $materials = $scheduledLesson->lesson
            ? $scheduledLesson->lesson->materials
            : $scheduledLesson->course->materials;

        return view('education.schedule.show', [
            'user' => $request->user(),
            'scheduled' => $scheduledLesson,
            'materials' => $materials,
            'attendance' => $this->scheduledLessonService->attendanceFor($request->user(), $scheduledLesson),
            'confirmedCount' => $scheduledLesson->attendances->where('status', 'confirmed')->count(),
            'declinedCount' => $scheduledLesson->attendances->where('status', 'declined')->count(),
        ]);
    }

    public function confirm(Request $request, ScheduledLesson $scheduledLesson): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);

        try {
            $this->scheduledLessonService->confirmAttendance($request->user(), $scheduledLesson);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['attendance' => $e->getMessage()]);
        }

        return back()->with('status', 'Attendance confirmed. See you on the video call!');
    }

    public function decline(Request $request, ScheduledLesson $scheduledLesson): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);

        try {
            $this->scheduledLessonService->declineAttendance($request->user(), $scheduledLesson);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['attendance' => $e->getMessage()]);
        }

        return back()->with('status', 'Marked as not attending.');
    }

    public function destroy(Request $request, ScheduledLesson $scheduledLesson): RedirectResponse
    {
        abort_unless($request->user()->canCreateCourses(), 403);

        try {
            $this->scheduledLessonService->cancel($scheduledLesson, $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['schedule' => $e->getMessage()]);
        }

        return redirect()
            ->route('education.schedule.index')
            ->with('status', 'Video lesson cancelled. Participants were notified.');
    }
}
