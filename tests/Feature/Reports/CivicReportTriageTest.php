<?php

namespace Tests\Feature\Reports;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceMembershipStatus;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\ReportLocation;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CivicReportTriageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_request_information_and_citizen_response_returns_report_to_queue(): void
    {
        $this->seed();
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);
        $citizen->markEmailAsVerified();
        $report = CivicReport::factory()->create(['user_id' => $citizen->id]);
        ReportLocation::factory()->create([
            'civic_report_id' => $report->id,
            'latitude' => 38.8977,
            'longitude' => -77.0365,
        ]);
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $administrator->markEmailAsVerified();

        $this->actingAs($administrator)
            ->put(route('staff.reports.triage', $report), [
                'status' => CivicReportStatus::NeedsInformation->value,
                'priority' => CivicReportPriority::High->value,
                'reason' => 'Please confirm the nearest intersection.',
                'is_public' => '1',
            ])
            ->assertRedirect(route('staff.reports.show', $report));

        $report->refresh();
        $this->assertSame(CivicReportStatus::NeedsInformation, $report->status);
        $this->assertSame(CivicReportPriority::High, $report->priority);
        $this->assertTrue($report->is_public);
        $this->assertSame(38.9, (float) $report->location->public_latitude);
        $this->assertSame(-77.04, (float) $report->location->public_longitude);

        $this->actingAs($citizen)
            ->post(route('citizen.reports.comments.store', $report), ['body' => 'It is at Oak Street and Pine Avenue.'])
            ->assertRedirect(route('citizen.reports.show', $report));

        $report->refresh();
        $this->assertSame(CivicReportStatus::Submitted, $report->status);
        $this->assertDatabaseHas('report_status_events', [
            'civic_report_id' => $report->id,
            'event_type' => 'citizen_information_provided',
            'from_status' => CivicReportStatus::NeedsInformation->value,
            'to_status' => CivicReportStatus::Submitted->value,
        ]);
        $this->assertDatabaseHas('report_comments', [
            'civic_report_id' => $report->id,
            'visibility' => 'public',
            'body' => 'It is at Oak Street and Pine Avenue.',
        ]);
    }

    public function test_staff_can_only_triage_reports_routed_to_an_active_unit_membership(): void
    {
        $this->seed();
        $requestedUnit = OrganizationUnit::factory()->create();
        $otherUnit = OrganizationUnit::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        $staff->markEmailAsVerified();
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $otherUnit->id,
            'membership_type' => RoleName::Staff->value,
            'status' => WorkforceMembershipStatus::Active->value,
        ]);
        $report = CivicReport::factory()->create(['routed_unit_id' => $requestedUnit->id]);

        $this->actingAs($staff)
            ->get(route('staff.reports.show', $report))
            ->assertForbidden();

        $this->actingAs($staff)
            ->put(route('staff.reports.triage', $report), [
                'status' => CivicReportStatus::Acknowledged->value,
                'priority' => CivicReportPriority::Normal->value,
                'is_public' => '0',
            ])
            ->assertForbidden();

        $this->assertSame(CivicReportStatus::Submitted, $report->fresh()->status);
    }

    public function test_staff_member_of_routed_unit_can_acknowledge_and_unpublish_location(): void
    {
        $this->seed();
        $unit = OrganizationUnit::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        $staff->markEmailAsVerified();
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => RoleName::Staff->value,
            'status' => WorkforceMembershipStatus::Active->value,
        ]);
        $report = CivicReport::factory()->create(['routed_unit_id' => $unit->id]);
        $location = ReportLocation::factory()->create([
            'civic_report_id' => $report->id,
            'latitude' => 38.8977,
            'longitude' => -77.0365,
            'public_latitude' => 38.9,
            'public_longitude' => -77.04,
            'privacy_level' => 'generalized_public',
        ]);
        $report->forceFill(['is_public' => true])->save();

        $this->actingAs($staff)
            ->get(route('staff.reports.show', $report))
            ->assertOk()
            ->assertSee(route('staff.reports.index'))
            ->assertSee('aria-current="page"', false);

        $this->actingAs($staff)
            ->put(route('staff.reports.triage', $report), [
                'status' => CivicReportStatus::Acknowledged->value,
                'priority' => CivicReportPriority::Normal->value,
                'is_public' => '0',
            ])
            ->assertRedirect(route('staff.reports.show', $report));

        $this->assertSame(CivicReportStatus::Acknowledged, $report->fresh()->status);
        $this->assertFalse($report->fresh()->is_public);
        $this->assertNull($location->fresh()->public_latitude);
        $this->assertNull($location->fresh()->public_longitude);
    }

    public function test_report_without_coordinates_cannot_be_published_on_the_public_map(): void
    {
        $this->seed();
        $report = CivicReport::factory()->create();
        ReportLocation::factory()->create([
            'civic_report_id' => $report->id,
            'latitude' => null,
            'longitude' => null,
            'address' => 'Civic building entrance',
        ]);
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($administrator)
            ->from(route('staff.reports.show', $report))
            ->put(route('staff.reports.triage', $report), [
                'status' => CivicReportStatus::Acknowledged->value,
                'priority' => CivicReportPriority::Normal->value,
                'is_public' => '1',
            ])
            ->assertRedirect(route('staff.reports.show', $report))
            ->assertSessionHasErrors('is_public');

        $this->assertSame(CivicReportStatus::Submitted, $report->fresh()->status);
        $this->assertFalse($report->fresh()->is_public);
        $this->assertNull($report->location->fresh()->public_latitude);
        $this->assertNull($report->location->fresh()->public_longitude);
    }
}
