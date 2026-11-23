<?php

namespace App\Models;

use Database\Factories\RoutingRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingRule extends Model
{
    /** @use HasFactory<RoutingRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'service_category_id', 'organization_unit_id', 'created_by', 'priority', 'is_active', 'valid_from', 'valid_until',
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

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'valid_from' => 'datetime', 'valid_until' => 'datetime'];
    }
}
