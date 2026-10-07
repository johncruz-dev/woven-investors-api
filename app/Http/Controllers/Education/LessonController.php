<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeLessonManagement($request, $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $this->learningPlatformService->addLesson($course, $validated);

        return back()->with('status', 'Lesson added.');
    }

    public function show(Request $request, Course $course, Lesson $lesson): View
    {
        abort_unless($lesson->course_id === $course->id, 404);
        abort_unless($request->user()->canViewLessonsAndAssignments(), 403);
        $this->learningPlatformService->ensureUserCanAccessCourse($request->user(), $course);

        if (! $lesson->is_published) {
            $isOwner = $request->user()->isAdmin() || $course->teacher_id === $request->user()->id;
            abort_unless($isOwner, 404);
        }

        $lesson->load('materials');

        return view('education.lessons.show', [
            'user' => $request->user(),
            'course' => $course,
            'lesson' => $lesson,
            'isOwner' => $request->user()->isAdmin() || $course->teacher_id === $request->user()->id,
        ]);
    }

    public function edit(Request $request, Course $course, Lesson $lesson): View
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $this->authorizeLessonManagement($request, $course);

        return view('education.lessons.edit', [
            'user' => $request->user(),
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }

    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $this->authorizeLessonManagement($request, $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $this->learningPlatformService->updateLesson($lesson, $validated);

        return redirect()
            ->route('education.lessons.show', [$course, $lesson])
            ->with('status', 'Lesson updated.');
    }

    public function destroy(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $this->authorizeLessonManagement($request, $course);

        $this->learningPlatformService->deleteLesson($lesson);

        return redirect()
            ->route('education.courses.show', $course)
            ->with('status', 'Lesson deleted.');
    }

    public function move(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $this->authorizeLessonManagement($request, $course);

        $validated = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ]);

        $this->learningPlatformService->moveLesson($lesson, $validated['direction']);

        return back()->with('status', 'Lesson order updated.');
    }

    public function complete(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($request->user()->canTrackLearningProgress(), 403);
        abort_unless($lesson->course_id === $course->id, 404);
        $this->learningPlatformService->ensureUserCanAccessCourse($request->user(), $course);
        abort_unless($lesson->is_published, 404);

        $this->learningPlatformService->markLessonComplete($lesson, $request->user());

        return back()->with('status', 'Lesson marked complete.');
    }

    private function authorizeLessonManagement(Request $request, Course $course): void
    {
        abort_unless($request->user()->canCreateCourses(), 403);
        abort_unless($request->user()->isAdmin() || $course->teacher_id === $request->user()->id, 403);
    }
}
