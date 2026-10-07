<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->canUploadLearningMaterials(), 403);
        abort_unless($request->user()->isAdmin() || $course->teacher_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $this->learningPlatformService->uploadMaterial(
            $course,
            $request->user(),
            $request->file('file'),
            $validated
        );

        return back()->with('status', 'Learning material uploaded.');
    }

    public function download(Request $request, LearningMaterial $material): StreamedResponse
    {
        $material->load('course');
        $this->learningPlatformService->ensureUserCanAccessCourse($request->user(), $material->course);

        return Storage::disk('local')->download($material->file_path, $material->original_name);
    }
}
