<?php

namespace App\Http\Controllers\Education\Admin;

use App\Http\Controllers\Controller;
use App\Services\LearningPlatformService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->canGenerateReports(), 403);

        return view('education.admin.reports', [
            'user' => $request->user(),
            'report' => $this->learningPlatformService->adminReport(),
        ]);
    }
}
