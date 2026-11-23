<?php

namespace App\Models;

use Database\Factories\TaskDependencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class TaskDependency extends Model
{
    /** @use HasFactory<TaskDependencyFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['work_task_id', 'depends_on_task_id', 'created_by', 'created_at'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'depends_on_task_id');
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Task dependencies are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Task dependencies are append-only.');
        });
    }
}
