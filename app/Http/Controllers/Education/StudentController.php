<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\EducationSupportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        private readonly EducationSupportService $educationSupportService,
    ) {}

    public function index(Request $request): View
    {
        $school = $this->educationSupportService->defaultSchool();
        abort_unless($school, 404, 'No school configured. Run the database seeder.');

        $students = $this->educationSupportService->paginateStudents(
            $school,
            $request->string('q')->toString() ?: null,
        );

        return view('education.students.index', [
            'user' => $request->user(),
            'school' => $school,
            'students' => $students,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function show(Request $request, Student $student): View
    {
        $student->load(['school', 'supportTickets' => fn ($q) => $q->latest()]);

        return view('education.students.show', [
            'user' => $request->user(),
            'student' => $student,
        ]);
    }
}
