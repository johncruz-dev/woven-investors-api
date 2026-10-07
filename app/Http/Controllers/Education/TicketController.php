<?php

namespace App\Http\Controllers\Education;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\EducationSupportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        private readonly EducationSupportService $educationSupportService,
    ) {}

    public function index(Request $request): View
    {
        $school = $this->educationSupportService->defaultSchool();
        abort_unless($school, 404, 'No school configured. Run the database seeder.');

        $status = $request->string('status')->toString() ?: null;

        $tickets = $this->educationSupportService->paginateTickets(
            $school,
            $request->user(),
            $status,
        );

        return view('education.tickets.index', [
            'user' => $request->user(),
            'school' => $school,
            'tickets' => $tickets,
            'status' => $status,
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $school = $this->educationSupportService->defaultSchool();
        abort_unless($school, 404);

        $user = $request->user();

        $students = $user->isStudent()
            ? collect([$user->studentProfile])->filter()
            : Student::query()->where('school_id', $school->id)->orderBy('name')->get();

        $staff = User::query()
            ->whereIn('role', ['admin', 'teacher', 'counselor'])
            ->orderBy('name')
            ->get();

        return view('education.tickets.create', [
            'user' => $user,
            'school' => $school,
            'students' => $students,
            'staff' => $staff,
            'priorities' => TicketPriority::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->educationSupportService->defaultSchool();
        abort_unless($school, 404);

        $user = $request->user();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $student = Student::query()->findOrFail($validated['student_id']);

        if ($user->isStudent()) {
            abort_unless($user->studentProfile?->id === $student->id, 403);
            $validated['assigned_to'] = null;
        }

        abort_unless($student->school_id === $school->id, 404);

        $ticket = $this->educationSupportService->createTicket($school, $student, $user, $validated);

        return redirect()
            ->route('education.tickets.show', $ticket)
            ->with('status', 'Support ticket created.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        $ticket->load(['student', 'assignee', 'creator', 'school']);
        $user = $request->user();

        if ($user->isStudent()) {
            abort_unless($user->studentProfile?->id === $ticket->student_id, 403);
        }

        $staff = User::query()
            ->whereIn('role', ['admin', 'teacher', 'counselor'])
            ->orderBy('name')
            ->get();

        return view('education.tickets.show', [
            'user' => $user,
            'ticket' => $ticket,
            'statuses' => TicketStatus::cases(),
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($request->user()->canManageTickets(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $assignee = isset($validated['assigned_to'])
            ? User::query()->find($validated['assigned_to'])
            : null;

        $this->educationSupportService->updateTicketStatus(
            $ticket,
            TicketStatus::from($validated['status']),
            $assignee,
        );

        return back()->with('status', 'Ticket updated.');
    }
}
