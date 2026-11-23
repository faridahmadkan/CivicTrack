<?php

namespace Tests\Feature;

use App\Enums\CivicReportStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceMembershipStatus;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\ReportLocation;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\ReportSlaService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportLifecycleTransitionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_cancel_only_before_field_work_is_planned(): void
    {
        $this->seed();
        $report = CivicReport::factory()->create();

        $this->withSession(['guest_report_access_'.$report->id => true])
            ->post(route('public.reports.cancel', $report), ['reason' => 'I submitted this report by mistake.'])
            ->assertRedirect(route('public.reports.track.show', $report));

        $this->assertSame(CivicReportStatus::Cancelled, $report->fresh()->status);
        $this->assertDatabaseHas('report_status_events', [
            'civic_report_id' => $report->id,
            'event_type' => 'report_cancelled',
            'to_status' => CivicReportStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'event_key' => 'civic_report.cancelled',
            'subject_id' => $report->id,
        ]);

        $anotherReport = CivicReport::factory()->create(['status' => CivicReportStatus::Acknowledged]);
        $unit = OrganizationUnit::factory()->create();
        WorkOrder::factory()->create([
            'civic_report_id' => $anotherReport->id,
            'organization_unit_id' => $unit->id,
        ]);

        $this->withSession(['guest_report_access_'.$anotherReport->id => true])
            ->from(route('public.reports.track.show', $anotherReport))
            ->post(route('public.reports.cancel', $anotherReport), ['reason' => 'The report is no longer needed.'])
            ->assertRedirect(route('public.reports.track.show', $anotherReport))
            ->assertSessionHasErrors('reason');

        $this->assertSame(CivicReportStatus::Acknowledged, $anotherReport->fresh()->status);
    }

    public function test_guest_cannot_cancel_without_a_verified_tracking_session(): void
    {
        $this->seed();
        $report = CivicReport::factory()->create();

        $this->post(route('public.reports.cancel', $report), ['reason' => 'I no longer need assistance.'])
            ->assertForbidden();

        $this->assertSame(CivicReportStatus::Submitted, $report->fresh()->status);
    }

    public function test_staff_can_review_a_dispute_by_upholding_or_reopening_the_original_outcome(): void
    {
        $this->seed();
        $unit = OrganizationUnit::factory()->create();
        $owner = User::factory()->create();
        $owner->assignRole(RoleName::Citizen->value);
        $resolvedReport = CivicReport::factory()->create([
            'user_id' => $owner->id,
            'routed_unit_id' => $unit->id,
            'status' => CivicReportStatus::Resolved,
            'resolved_at' => now()->subDay(),
            'is_public' => true,
        ]);
        $location = ReportLocation::factory()->create([
            'civic_report_id' => $resolvedReport->id,
            'latitude' => 34.5553,
            'longitude' => 69.2075,
            'public_latitude' => 34.56,
            'public_longitude' => 69.21,
            'privacy_level' => 'generalized_public',
        ]);

        $this->actingAs($owner)
            ->post(route('public.reports.disputes.store', $resolvedReport), ['reason' => 'The reported leak is still active.'])
            ->assertRedirect(route('citizen.reports.show', $resolvedReport));

        $this->assertSame(CivicReportStatus::Disputed, $resolvedReport->fresh()->status);
        $this->assertNull($location->fresh()->public_latitude);

        $otherUnit = OrganizationUnit::factory()->create();
        $unassignedStaff = User::factory()->create();
        $unassignedStaff->assignRole(RoleName::Staff->value);
        UnitMembership::factory()->create([
            'user_id' => $unassignedStaff->id,
            'organization_unit_id' => $otherUnit->id,
            'membership_type' => RoleName::Staff->value,
            'status' => WorkforceMembershipStatus::Active->value,
        ]);
        $this->actingAs($unassignedStaff)
            ->put(route('staff.reports.disputes.review', $resolvedReport), [
                'decision' => 'uphold',
                'reason' => 'The field inspection confirms service completion.',
            ])
            ->assertForbidden();

        $administrator = $this->administrator();
        $this->actingAs($administrator)
            ->put(route('staff.reports.disputes.review', $resolvedReport), [
                'decision' => 'uphold',
                'reason' => 'The field inspection confirms service completion.',
            ])
            ->assertRedirect(route('staff.reports.show', $resolvedReport));

        $this->assertSame(CivicReportStatus::Resolved, $resolvedReport->fresh()->status);
        $this->assertTrue($resolvedReport->fresh()->is_public);
        $this->assertSame(34.56, (float) $location->fresh()->public_latitude);

        $reportToReopen = CivicReport::factory()->create([
            'status' => CivicReportStatus::Rejected,
            'closed_at' => now()->subDay(),
        ]);
        app(ReportSlaService::class)->startForReport($reportToReopen);

        $this->app['auth']->guard()->logout();
        $this->withSession(['guest_report_access_'.$reportToReopen->id => true])
            ->post(route('public.reports.disputes.store', $reportToReopen), ['reason' => 'The report was rejected before an inspection.'])
            ->assertRedirect(route('public.reports.track.show', $reportToReopen));

        $this->actingAs($administrator)
            ->put(route('staff.reports.disputes.review', $reportToReopen), [
                'decision' => 'reopen',
                'reason' => 'The evidence supports reopening field review.',
            ])
            ->assertRedirect(route('staff.reports.show', $reportToReopen));

        $this->assertSame(CivicReportStatus::Reopened, $reportToReopen->fresh()->status);
        $this->assertSame(2, $reportToReopen->slaInstances()->count());
        $this->assertDatabaseHas('audit_events', [
            'event_key' => 'civic_report.dispute_reviewed',
            'subject_id' => $reportToReopen->id,
        ]);
    }

    private function administrator(): User
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $administrator->markEmailAsVerified();

        return $administrator;
    }
}
