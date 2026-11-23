<?php

namespace App\Services;

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationUnitService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    /**
     * @param array{code: string, name: string, unit_type: string, parent_id?: int|string|null, description?: string|null} $attributes
     */
    public function create(User $actor, array $attributes): OrganizationUnit
    {
        return DB::transaction(function () use ($actor, $attributes): OrganizationUnit {
            $parent = $this->findActiveParent($attributes['parent_id'] ?? null);
            $unit = OrganizationUnit::query()->create([
                ...$attributes,
                'parent_id' => $parent?->id,
                'created_by' => $actor->id,
                'is_active' => true,
            ]);

            $this->auditWriter->record(
                'organization_unit.created',
                $unit,
                ['code' => $unit->code, 'unit_type' => $unit->unit_type->value],
                $actor,
            );

            return $unit;
        });
    }

    /**
     * @param array{code: string, name: string, unit_type: string, parent_id?: int|string|null, description?: string|null, is_active?: bool|string} $attributes
     */
    public function update(OrganizationUnit $organizationUnit, User $actor, array $attributes): OrganizationUnit
    {
        return DB::transaction(function () use ($organizationUnit, $actor, $attributes): OrganizationUnit {
            $organizationUnit = OrganizationUnit::query()->lockForUpdate()->findOrFail($organizationUnit->id);
            $parentId = $attributes['parent_id'] ?? null;
            $this->assertNoParentCycle($organizationUnit, $parentId);
            $parent = $this->findActiveParent($parentId);

            $organizationUnit->update([
                ...$attributes,
                'parent_id' => $parent?->id,
                'is_active' => filter_var($attributes['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ]);

            $this->auditWriter->record(
                'organization_unit.updated',
                $organizationUnit,
                ['code' => $organizationUnit->code, 'is_active' => $organizationUnit->is_active],
                $actor,
            );

            return $organizationUnit;
        });
    }

    private function findActiveParent(int|string|null $parentId): ?OrganizationUnit
    {
        if ($parentId === null || $parentId === '') {
            return null;
        }

        $parent = OrganizationUnit::query()
            ->whereKey($parentId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if ($parent === null) {
            throw ValidationException::withMessages(['parent_id' => 'Choose an active parent organization unit.']);
        }

        return $parent;
    }

    private function assertNoParentCycle(OrganizationUnit $unit, int|string|null $parentId): void
    {
        $visited = [];

        while ($parentId !== null && $parentId !== '') {
            if ((int) $parentId === $unit->id || isset($visited[$parentId])) {
                throw ValidationException::withMessages(['parent_id' => 'The parent would create an invalid organization hierarchy.']);
            }

            $visited[$parentId] = true;
            $parentId = OrganizationUnit::query()->whereKey($parentId)->value('parent_id');
        }
    }
}
