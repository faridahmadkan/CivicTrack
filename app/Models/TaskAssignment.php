<?php

namespace App\Models;

use App\Enums\TaskAssignmentStatus;
use Database\Factories\TaskAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TaskAssignment extends Model
{
    /** @use HasFactory<TaskAssignmentFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(static function (TaskAssignment $assignment): void {
            if ($assignment->isDirty(['worker_id', 'ended_at'])) {
                $assignment->active_worker_id = $assignment->ended_at === null ? $assignment->worker_id : null;
            }
        });
    }

    protected $fillable = [
        'work_task_id', 'worker_id', 'assigned_by', 'status', 'assignment_instructions', 'end_reason',
        'assigned_at', 'accepted_at', 'started_at', 'ended_at',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function workerRating(): HasOne
    {
        return $this->hasOne(WorkerRating::class);
    }

    protected function casts(): array
    {
        return [
            'status' => TaskAssignmentStatus::class,
            'assigned_at' => 'datetime',
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}
