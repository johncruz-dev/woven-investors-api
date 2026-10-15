<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledLesson extends Model
{
    protected $fillable = [
        'course_id',
        'teacher_id',
        'lesson_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'meeting_url',
        'status',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ScheduledLessonAttendance::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'scheduled_lesson_participants')
            ->withTimestamps();
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isLive(): bool
    {
        return $this->isScheduled()
            && $this->starts_at->lte(now())
            && $this->ends_at->gte(now());
    }

    public function isUpcoming(): bool
    {
        return $this->isScheduled() && $this->starts_at->isFuture();
    }

    public function canJoinNow(): bool
    {
        if (! $this->isScheduled()) {
            return false;
        }

        return now()->between(
            $this->starts_at->copy()->subMinutes(15),
            $this->ends_at,
        );
    }
}
