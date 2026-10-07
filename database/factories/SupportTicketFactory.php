<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\School;
use App\Models\Student;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'created_by' => User::factory()->teacher(),
            'assigned_to' => null,
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Normal,
            'resolved_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Open,
            'resolved_at' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }
}
