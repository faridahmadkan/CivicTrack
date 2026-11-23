<?php

namespace App\Models;

use App\Enums\WorkTaskStatus;
use Database\Factories\TaskUpdateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class TaskUpdate extends Model
{
    /** @use HasFactory<TaskUpdateFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'work_task_id', 'task_assignment_id', 'actor_id', 'event_type', 'from_status', 'to_status', 'body', 'occurred_at',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return [
            'from_status' => WorkTaskStatus::class,
            'to_status' => WorkTaskStatus::class,
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Task updates are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Task updates are append-only.');
        });
    }
}
