<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleName;
use App\Models\OrganizationUnit;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ServiceCategoryManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_create_and_route_a_service_category(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $unit = OrganizationUnit::factory()->create();

        $this->actingAs($administrator)
            ->post(route('admin.service-categories.store'), [
                'code' => 'DRAIN',
                'name' => 'Drainage services',
                'description' => 'Blocked drains and local flooding reports.',
                'default_unit_id' => $unit->id,
                'requires_location' => '1',
                'is_public' => '1',
                'is_active' => '1',
                'sort_order' => '10',
            ])
            ->assertRedirect(route('admin.service-categories.index'));

        $category = ServiceCategory::query()->where('code', 'DRAIN')->firstOrFail();
        $this->assertSame($unit->id, $category->default_unit_id);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $administrator->id,
            'event_key' => 'service_category.created',
            'subject_id' => $category->id,
        ]);
        $this->actingAs($administrator)
            ->get(route('admin.service-categories.index', [
                'q' => 'Drainage',
                'status' => 'active',
                'visibility' => 'public',
            ]))
            ->assertOk()
            ->assertSee('Drainage services');
    }

    public function test_non_administrator_cannot_change_the_service_catalog(): void
    {
        $this->seed();
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);
        $category = ServiceCategory::query()->firstOrFail();

        $this->actingAs($citizen)
            ->put(route('admin.service-categories.update', $category), [
                'code' => 'MOVED',
                'name' => 'Changed name',
                'requires_location' => '1',
                'is_public' => '1',
                'is_active' => '1',
                'sort_order' => '0',
            ])
            ->assertForbidden();

        $this->assertSame('ROAD', $category->fresh()->code);
    }
}
