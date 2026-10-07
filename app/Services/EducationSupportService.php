<?php

namespace App\Services;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\School;
use App\Models\Student;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EducationSupportService
{
    public function dashboardMetrics(School $school): array
    {
        $tickets = SupportTicket::query()->where('school_id', $school->id);

        return [
            'students' => Student::query()->where('school_id', $school->id)->count(),
            'open_tickets' => (clone $tickets)->whereIn('status', [
                TicketStatus::Open->value,
                TicketStatus::InProgress->value,
            ])->count(),
            'resolved_tickets' => (clone $tickets)->where('status', TicketStatus::Resolved->value)->count(),
            'high_priority_open' => (clone $tickets)->where('priority', TicketPriority::High->value)
                ->whereIn('status', [
                    TicketStatus::Open->value,
                    TicketStatus::InProgress->value,
                ])->count(),
        ];
    }

    public function paginateStudents(School $school, ?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Student::query()
            ->where('school_id', $school->id)
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('external_id', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'supportTickets as open_tickets_count' => function ($query) {
                    $query->whereIn('status', [
                        TicketStatus::Open->value,
                        TicketStatus::InProgress->value,
                    ]);
                },
            ])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateTickets(
        School $school,
        User $user,
        ?string $status = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return SupportTicket::query()
            ->where('school_id', $school->id)
            ->with(['student', 'assignee', 'creator'])
            ->when($user->isStudent(), function ($query) use ($user) {
                $studentId = $user->studentProfile?->id;
                $query->where('student_id', $studentId ?? 0);
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createTicket(
        School $school,
        Student $student,
        User $creator,
        array $data,
    ): SupportTicket {
        return SupportTicket::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'created_by' => $creator->id,
            'assigned_to' => $data['assigned_to'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::from($data['priority'] ?? TicketPriority::Normal->value),
        ]);
    }

    public function updateTicketStatus(SupportTicket $ticket, TicketStatus $status, ?User $assignee = null): SupportTicket
    {
        return DB::transaction(function () use ($ticket, $status, $assignee) {
            $ticket->status = $status;
            $ticket->resolved_at = $status === TicketStatus::Resolved || $status === TicketStatus::Closed
                ? ($ticket->resolved_at ?? now())
                : null;

            if ($assignee !== null) {
                $ticket->assigned_to = $assignee->id;
            } elseif ($status === TicketStatus::InProgress && $ticket->assigned_to === null && auth()->id()) {
                $ticket->assigned_to = auth()->id();
            }

            $ticket->save();

            return $ticket->fresh(['student', 'assignee', 'creator']);
        });
    }

    public function defaultSchool(): ?School
    {
        return School::query()->orderBy('id')->first();
    }
}
