<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Services\EducationSupportService;
use App\Services\LearningPlatformService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly EducationSupportService $educationSupportService,
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function index(Request $request): View
    {
        $school = $this->educationSupportService->defaultSchool();
        $user = $request->user();

        $metrics = $school
            ? $this->educationSupportService->dashboardMetrics($school)
            : ['students' => 0, 'open_tickets' => 0, 'resolved_tickets' => 0, 'high_priority_open' => 0];

        $recentTickets = $school
            ? $this->educationSupportService->paginateTickets($school, $user, null, 5)
            : null;

        $learning = $user->isStudent()
            ? $this->learningPlatformService->studentCourseSummaries($user)
            : [];

        return view('education.dashboard', [
            'user' => $user,
            'school' => $school,
            'metrics' => $metrics,
            'recentTickets' => $recentTickets,
            'learning' => $learning,
        ]);
    }
}
