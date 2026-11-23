<?php

namespace Tests\Feature;

use App\Enums\CivicReportStatus;
use App\Models\CivicReport;
use App\Models\ReportLocation;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicCommunityMapTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_map_only_uses_staff_approved_generalized_coordinates(): void
    {
        $this->seed();
        $publicReport = CivicReport::factory()->create([
            'status' => CivicReportStatus::InProgress,
            'is_public' => true,
        ]);
        ReportLocation::factory()->create([
            'civic_report_id' => $publicReport->id,
            'latitude' => 38.8977,
            'longitude' => -77.0365,
            'public_latitude' => 38.9,
            'public_longitude' => -77.04,
            'privacy_level' => 'generalized_public',
            'address' => 'Private entrance details',
        ]);
        $privateCategory = ServiceCategory::factory()->create(['is_public' => false]);
        $privateReport = CivicReport::factory()->create([
            'service_category_id' => $privateCategory->id,
            'is_public' => false,
            'title' => 'Private report title',
        ]);
        ReportLocation::factory()->create([
            'civic_report_id' => $privateReport->id,
            'latitude' => 40.1234567,
            'longitude' => -71.9876543,
            'public_latitude' => null,
            'public_longitude' => null,
            'privacy_level' => 'precise_private',
        ]);

        $response = $this->get(route('public.map.index'))
            ->assertOk()
            ->assertSee('Published service reports')
            ->assertSee('1 published report')
            ->assertSee($publicReport->category->name)
            ->assertDontSee('Private report title')
            ->assertDontSee($publicReport->public_reference)
            ->assertDontSee('38.8977000')
            ->assertDontSee('-77.0365000')
            ->assertDontSee('Private entrance details')
            ->assertDontSee($privateReport->category->name);

        preg_match('/<script type="application\/json" id="community-report-markers">(.*?)<\/script>/s', $response->getContent(), $matches);

        $this->assertSame([
            [
                'latitude' => 38.9,
                'longitude' => -77.04,
                'category' => $publicReport->category->name,
                'status' => 'In progress',
            ],
        ], json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR));
    }
}
