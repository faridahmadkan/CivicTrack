<?php

namespace App\Models;

use Database\Factories\ReportSlaPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSlaPolicy extends Model
{
    /** @use HasFactory<ReportSlaPolicyFactory> */
    use HasFactory;

    protected $fillable = [
        'service_category_id', 'organization_unit_id', 'created_by', 'name', 'priority', 'version',
        'first_response_minutes', 'resolution_minutes', 'escalation_repeat_minutes', 'is_active', 'effective_at', 'expires_at',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(ReportSlaInstance::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'effective_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
