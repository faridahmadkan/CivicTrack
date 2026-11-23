<?php

namespace App\Models;

use App\Enums\OrganizationUnitType;
use Database\Factories\OrganizationUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationUnit extends Model
{
    /** @use HasFactory<OrganizationUnitFactory> */
    use HasFactory;

    protected $fillable = ['parent_id', 'created_by', 'code', 'name', 'unit_type', 'description', 'is_active'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(UnitMembership::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class, 'default_unit_id');
    }

    public function routingRules(): HasMany
    {
        return $this->hasMany(RoutingRule::class);
    }

    public function civicReports(): HasMany
    {
        return $this->hasMany(CivicReport::class, 'routed_unit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'unit_type' => OrganizationUnitType::class,
            'is_active' => 'boolean',
        ];
    }
}
