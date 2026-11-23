<?php

namespace App\Models;

use App\Enums\CivicReportPriority;
use App\Enums\WorkTaskStatus;
use Database\Factories\WorkTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTask extends Model
{
    /** @use HasFactory<WorkTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'work_order_id', 'created_by', 'task_number', 'title', 'instructions', 'status', 'priority', 'sequence',
        'due_at', 'started_at', 'submitted_at', 'verified_at', 'completed_at',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'task_dependencies', 'work_task_id', 'depends_on_task_id');
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'task_dependencies', 'depends_on_task_id', 'work_task_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class)->orderBy('assigned_at')->orderBy('id');
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class)->whereNull('ended_at')->orderBy('assigned_at')->orderBy('id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(TaskUpdate::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(TaskEvidence::class)->orderBy('created_at')->orderBy('id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(TaskVerification::class)->orderBy('reviewed_at')->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'status' => WorkTaskStatus::class,
            'priority' => CivicReportPriority::class,
            'due_at' => 'datetime',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
