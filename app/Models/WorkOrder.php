<?php

namespace App\Models;

use App\Enums\WorkOrderStatus;
use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory;

    protected $fillable = [
        'civic_report_id', 'organization_unit_id', 'created_by', 'reference', 'title', 'instructions', 'status', 'due_at', 'completed_at',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkTask::class)->orderBy('sequence')->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'status' => WorkOrderStatus::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
