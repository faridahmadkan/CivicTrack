<?php

namespace App\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id', 'default_unit_id', 'created_by', 'code', 'name', 'slug', 'description',
        'requires_location', 'is_public', 'is_active', 'sort_order',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class, 'default_unit_id');
    }

    public function routingRules(): HasMany
    {
        return $this->hasMany(RoutingRule::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CivicReport::class);
    }

    protected function casts(): array
    {
        return [
            'requires_location' => 'boolean',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
