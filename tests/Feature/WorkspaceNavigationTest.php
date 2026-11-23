<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkspaceNavigationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_map_has_a_safe_home_link_and_marks_its_navigation_item_current(): void
    {
        $this->seed();
        config()->set('civictrack.map.street_tile_url', 'https://tile.example.test/{z}/{x}/{y}.png');
        config()->set('civictrack.map.satellite_tile_url', 'https://satellite.example.test/{z}/{y}/{x}');

        $this->get(route('public.map.index'))
            ->assertOk()
            ->assertSee(route('public.home'))
            ->assertSee('CivicTrack home')
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-street-tile-url="https://tile.example.test/{z}/{x}/{y}.png"', false)
            ->assertSee('data-satellite-tile-url="https://satellite.example.test/{z}/{y}/{x}"', false)
            ->assertSee('Satellite');
    }

    public function test_map_explains_that_satellite_imagery_is_unavailable_when_no_url_is_configured(): void
    {
        $this->seed();
        config()->set('civictrack.map.satellite_tile_url', '');

        $this->get(route('public.map.index'))
            ->assertOk()
            ->assertSee('data-satellite-tile-url=""', false)
            ->assertSee('Satellite imagery is not configured. Street map is active.')
            ->assertSee('disabled', false);
    }

    public function test_nested_citizen_report_page_marks_reports_current_and_returns_to_its_list(): void
    {
        $this->seed();
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);
        $report = CivicReport::factory()->create(['user_id' => $citizen->id]);

        $response = $this->actingAs($citizen)
            ->get(route('citizen.reports.show', $report))
            ->assertOk()
            ->assertSee(route('citizen.reports.index'))
            ->assertSee('My reports')
            ->assertSee('aria-current="page"', false);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
