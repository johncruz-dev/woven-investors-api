<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledLessonAttendance extends Model
{
    protected $fillable = [
        'scheduled_lesson_id',
        'user_id',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function scheduledLesson(): BelongsTo
    {
        return $this->belongsTo(ScheduledLesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }
}
