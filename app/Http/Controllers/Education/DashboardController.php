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

        $perPage = (int) $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $recentTickets = $school
            ? $this->educationSupportService->paginateTickets($school, $user, null, $perPage)
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
