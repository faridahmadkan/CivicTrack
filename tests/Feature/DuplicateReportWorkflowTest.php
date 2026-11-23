<?php

namespace Tests\Feature;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceMembershipStatus;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\ReportLocation;
use App\Models\ReportStatusEvent;
use App\Models\ServiceCategory;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DuplicateReportWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_duplicate_suggestions_are_advisory_until_staff_reviews_them(): void
    {
        $this->seed();
        $category = ServiceCategory::factory()->create();
        $report = CivicReport::factory()->create([
            'service_category_id' => $category->id,
            'title' => 'Water leak near school',
        ]);
        $candidate = CivicReport::factory()->create([
            'service_category_id' => $category->id,
            'title' => 'Water leak near school gate',
        ]);
        ReportLocation::factory()->create([
            'civic_report_id' => $report->id,
            'latitude' => 34.5553,
            'longitude' => 69.2075,
        ]);
        ReportLocation::factory()->create([
            'civic_report_id' => $candidate->id,
            'latitude' => 34.5554,
            'longitude' => 69.2076,
        ]);
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route('staff.reports.show', $report))
            ->assertOk()
            ->assertSee('Possible duplicates')
            ->assertSee($candidate->public_reference)
            ->assertSee('They do not change report status.');

        $this->assertSame(CivicReportStatus::Submitted, $report->fresh()->status);
        $this->assertSame(CivicReportStatus::Submitted, $candidate->fresh()->status);
        $this->assertDatabaseCount('report_relationships', 0);
    }

    public function test_staff_can_link_related_reports_without_changing_their_status_or_history(): void
    {
        $this->seed();
        $report = CivicReport::factory()->create();
        $relatedReport = CivicReport::factory()->create();
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->post(route('staff.reports.related.store', [$report, $relatedReport]), [
                'reason' => 'The reports concern the same road segment.',
            ])
            ->assertRedirect(route('staff.reports.show', $report));

        $this->assertDatabaseHas('report_relationships', [
            'report_id' => min($report->id, $relatedReport->id),
            'related_report_id' => max($report->id, $relatedReport->id),
            'relationship_type' => 'related',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'event_key' => 'civic_report.related_linked',
            'subject_id' => $report->id,
        ]);
        $this->assertSame(CivicReportStatus::Submitted, $report->fresh()->status);
        $this->assertSame(CivicReportStatus::Submitted, $relatedReport->fresh()->status);
        $this->assertSame(0, $report->statusEvents()->count());

        $this->actingAs($administrator)
            ->from(route('staff.reports.show', $relatedReport))
            ->post(route('staff.reports.related.store', [$relatedReport, $report]), [
                'reason' => 'A reciprocal link should be idempotently prevented.',
            ])
            ->assertRedirect(route('staff.reports.show', $relatedReport))
            ->assertSessionHasErrors('related_report');

        $this->assertDatabaseCount('report_relationships', 1);
    }

    public function test_staff_can_mark_duplicate_and_merge_without_deleting_reports_or_history(): void
    {
        $this->seed();
        $duplicate = CivicReport::factory()->create();
        $canonical = CivicReport::factory()->create(['status' => CivicReportStatus::Acknowledged]);
        ReportStatusEvent::factory()->create(['civic_report_id' => $duplicate->id]);
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->post(route('staff.reports.duplicates.store', [$duplicate, $canonical]), [
                'reason' => 'Both reports describe the same damaged water pipe.',
            ])
            ->assertRedirect(route('staff.reports.show', $duplicate));

        $this->assertSame(CivicReportStatus::Duplicate, $duplicate->fresh()->status);
        $this->assertSame(CivicReportStatus::Acknowledged, $canonical->fresh()->status);
        $this->assertDatabaseHas('report_relationships', [
            'report_id' => $duplicate->id,
            'related_report_id' => $canonical->id,
            'relationship_type' => 'duplicate',
        ]);
        $this->assertSame(2, $duplicate->statusEvents()->count());

        $mergeableReport = CivicReport::factory()->create();
        ReportStatusEvent::factory()->create(['civic_report_id' => $mergeableReport->id]);
        $this->actingAs($administrator)
            ->post(route('staff.reports.duplicates.merge', [$mergeableReport, $canonical]), [
                'reason' => 'Keep one canonical case while preserving the original filing.',
            ])
            ->assertRedirect(route('staff.reports.show', $mergeableReport));

        $this->assertSame(CivicReportStatus::Duplicate, $mergeableReport->fresh()->status);
        $this->assertDatabaseHas('report_relationships', [
            'report_id' => $mergeableReport->id,
            'related_report_id' => $canonical->id,
            'relationship_type' => 'merged_into',
        ]);
        $this->assertSame(2, $mergeableReport->statusEvents()->count());
        $this->assertSame(3, CivicReport::query()->count());
        $this->assertDatabaseHas('audit_events', [
            'event_key' => 'civic_report.duplicate_merged',
            'subject_id' => $mergeableReport->id,
        ]);
    }

    public function test_staff_cannot_link_a_report_outside_their_department(): void
    {
        $this->seed();
        $staffUnit = OrganizationUnit::factory()->create();
        $otherUnit = OrganizationUnit::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $staffUnit->id,
            'membership_type' => RoleName::Staff->value,
            'status' => WorkforceMembershipStatus::Active->value,
        ]);
        $report = CivicReport::factory()->create(['routed_unit_id' => $staffUnit->id]);
        $otherReport = CivicReport::factory()->create(['routed_unit_id' => $otherUnit->id]);

        $this->actingAs($staff)
            ->post(route('staff.reports.related.store', [$report, $otherReport]), [
                'reason' => 'Trying to access another department report.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('report_relationships', 0);
        $this->assertSame(CivicReportStatus::Submitted, $report->fresh()->status);
    }

    public function test_duplicate_status_is_not_available_through_general_triage(): void
    {
        $this->seed();
        $report = CivicReport::factory()->create();
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->from(route('staff.reports.show', $report))
            ->put(route('staff.reports.triage', $report), [
                'status' => CivicReportStatus::Duplicate->value,
                'priority' => CivicReportPriority::Normal->value,
                'reason' => 'No canonical report is attached.',
                'is_public' => '0',
            ])
            ->assertRedirect(route('staff.reports.show', $report))
            ->assertSessionHasErrors('status');

        $this->assertSame(CivicReportStatus::Submitted, $report->fresh()->status);
        $this->assertDatabaseCount('report_relationships', 0);
    }

    private function administrator(): User
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $administrator->markEmailAsVerified();

        return $administrator;
    }
}
