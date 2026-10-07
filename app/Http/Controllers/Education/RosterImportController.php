<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Services\EducationSupportService;
use App\Services\StudentRosterImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class RosterImportController extends Controller
{
    public function __construct(
        private readonly StudentRosterImportService $rosterImportService,
        private readonly EducationSupportService $educationSupportService,
    ) {}

    public function create(Request $request): View
    {
        abort_unless($request->user()->canImportRoster(), 403);

        return view('education.roster.import', [
            'user' => $request->user(),
            'school' => $this->educationSupportService->defaultSchool(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canImportRoster(), 403);

        $school = $this->educationSupportService->defaultSchool();
        abort_unless($school, 404, 'No school configured. Run the database seeder.');

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:'.config('security.upload.max_size_kb', 10240)],
        ]);

        try {
            $result = $this->rosterImportService->import($school, $request->file('file'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()
            ->route('education.students.index')
            ->with('status', "Roster import complete ({$result['students_upserted']} rows).");
    }
}
