<?php

namespace App\Services;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceCatalogService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    /** @param array{code: string, name: string, description?: string|null, default_unit_id?: int|string|null, requires_location: bool|string, is_public: bool|string, is_active: bool|string, sort_order: int|string} $attributes */
    public function save(?ServiceCategory $category, array $attributes, User $actor): ServiceCategory
    {
        return DB::transaction(function () use ($category, $attributes, $actor): ServiceCategory {
            $category ??= new ServiceCategory;
            $isNew = ! $category->exists;
            $category->fill([
                'code' => strtoupper($attributes['code']),
                'name' => trim($attributes['name']),
                'slug' => Str::slug($attributes['name'].'-'.$attributes['code']),
                'description' => isset($attributes['description']) ? trim($attributes['description']) : null,
                'default_unit_id' => $attributes['default_unit_id'] ?? null,
                'requires_location' => (bool) $attributes['requires_location'],
                'is_public' => (bool) $attributes['is_public'],
                'is_active' => (bool) $attributes['is_active'],
                'sort_order' => (int) $attributes['sort_order'],
                'created_by' => $category->created_by ?? $actor->id,
            ]);
            $category->save();

            $this->auditWriter->record(
                $isNew ? 'service_category.created' : 'service_category.updated',
                $category,
                ['code' => $category->code, 'is_active' => $category->is_active, 'default_unit_id' => $category->default_unit_id],
                $actor,
            );

            return $category;
        });
    }
}
