<?php

namespace App\Models;

use App\Enums\TaskVerificationDecision;
use Database\Factories\TaskVerificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class TaskVerification extends Model
{
    /** @use HasFactory<TaskVerificationFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['work_task_id', 'task_assignment_id', 'reviewed_by', 'decision', 'reason', 'reviewed_at'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected function casts(): array
    {
        return ['decision' => TaskVerificationDecision::class, 'reviewed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Task verifications are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Task verifications are append-only.');
        });
    }
}
