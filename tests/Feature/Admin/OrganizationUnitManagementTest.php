<?php

namespace Tests\Feature\Admin;

use App\Enums\OrganizationUnitType;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Models\OrganizationUnit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrganizationUnitManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_create_nested_service_units(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($administrator)->post(route('admin.organization-units.store'), [
            'code' => 'PUBLIC-WORKS',
            'name' => 'Public Works Department',
            'unit_type' => OrganizationUnitType::Department->value,
            'description' => 'Coordinates public infrastructure services.',
        ])->assertRedirect(route('admin.organization-units.index'));

        $department = OrganizationUnit::query()->where('code', 'PUBLIC-WORKS')->firstOrFail();

        $this->assertSame(OrganizationUnitType::Department, $department->unit_type);
        $this->assertSame($administrator->id, $department->created_by);
        $this->actingAs($administrator)
            ->get(route('admin.organization-units.index', [
                'q' => 'Public Works',
                'type' => OrganizationUnitType::Department->value,
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Public Works Department');

        $this->actingAs($administrator)->post(route('admin.organization-units.store'), [
            'code' => 'ROADS-DIVISION',
            'name' => 'Roads Division',
            'unit_type' => OrganizationUnitType::Division->value,
            'parent_id' => $department->id,
        ])->assertRedirect(route('admin.organization-units.index'));

        $division = OrganizationUnit::query()->where('code', 'ROADS-DIVISION')->firstOrFail();
        $this->assertSame($department->id, $division->parent_id);
        $this->assertSame('Roads Division', $department->children()->firstOrFail()->name);
    }

    public function test_administrator_cannot_create_a_circular_parent_relationship(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $parent = OrganizationUnit::factory()->create();
        $child = OrganizationUnit::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($administrator)
            ->put(route('admin.organization-units.update', $parent), [
                'code' => $parent->code,
                'name' => $parent->name,
                'unit_type' => $parent->unit_type->value,
                'parent_id' => $child->id,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($parent->fresh()->parent_id);
    }

    public function test_deactivating_a_unit_preserves_existing_membership_history(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $member = User::factory()->create();
        $member->assignRole(RoleName::Staff->value);
        $unit = OrganizationUnit::factory()->create();
        $membership = UnitMembership::factory()->create([
            'user_id' => $member->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => WorkforceApplicationType::Staff,
        ]);

        $this->actingAs($administrator)->put(route('admin.organization-units.update', $unit), [
            'code' => $unit->code,
            'name' => $unit->name,
            'unit_type' => $unit->unit_type->value,
            'parent_id' => null,
            'is_active' => '0',
        ])->assertRedirect(route('admin.organization-units.index'));

        $this->assertFalse($unit->fresh()->is_active);
        $this->assertDatabaseHas('unit_memberships', [
            'id' => $membership->id,
            'user_id' => $member->id,
            'organization_unit_id' => $unit->id,
        ]);
    }
}
