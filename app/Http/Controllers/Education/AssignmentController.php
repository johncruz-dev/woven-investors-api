<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->canCreateAssessments(), 403);
        abort_unless($request->user()->isAdmin() || $course->teacher_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'max_points' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'due_at' => ['nullable', 'date'],
        ]);

        $this->learningPlatformService->createAssignment($course, $request->user(), $validated);

        return back()->with('status', 'Assessment created.');
    }

    public function show(Request $request, Assignment $assignment): View
    {
        $assignment->load(['course', 'submissions.student']);
        abort_unless($request->user()->canViewLessonsAndAssignments(), 403);
        $this->learningPlatformService->ensureUserCanAccessCourse($request->user(), $assignment->course);

        $ownSubmission = $assignment->submissions
            ->firstWhere('student_id', $request->user()->id);

        return view('education.assignments.show', [
            'user' => $request->user(),
            'assignment' => $assignment,
            'ownSubmission' => $ownSubmission,
        ]);
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless($request->user()->canSubmitHomework(), 403);

        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:10000'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        try {
            $this->learningPlatformService->submitHomework(
                $assignment,
                $request->user(),
                $validated,
                $request->file('file')
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['content' => $e->getMessage()]);
        }

        return back()->with('status', 'Homework submitted.');
    }

    public function grade(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        abort_unless($request->user()->canGradeAssignments(), 403);

        $submission->load('assignment.course');
        abort_unless(
            $request->user()->isAdmin() || $submission->assignment->course->teacher_id === $request->user()->id,
            403
        );

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:'.$submission->assignment->max_points],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->learningPlatformService->gradeSubmission($submission, $request->user(), $validated);

        return back()->with('status', 'Submission graded.');
    }
}
