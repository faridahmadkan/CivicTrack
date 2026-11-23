<?php

namespace Tests\Feature;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use App\Enums\ReportCommentVisibility;
use App\Enums\RoleName;
use App\Enums\TaskAssignmentStatus;
use App\Enums\WorkforceMembershipStatus;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\ReportComment;
use App\Models\ReportLocation;
use App\Models\ReportSlaInstance;
use App\Models\ReportStatusEvent;
use App\Models\ServiceCategory;
use App\Models\TaskAssignment;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportUiAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_citizen_timeline_contains_only_citizen_visible_events_and_public_comments(): void
    {
        $this->seed();
        $citizen = $this->citizen();
        $report = CivicReport::factory()->create(['user_id' => $citizen->id]);

        ReportStatusEvent::factory()->create([
            'civic_report_id' => $report->id,
            'event_type' => 'status_changed',
            'to_status' => CivicReportStatus::UnderReview,
            'reason' => 'The service team began reviewing this report.',
            'citizen_visible' => true,
            'occurred_at' => now()->subHour(),
        ]);
        ReportStatusEvent::factory()->create([
            'civic_report_id' => $report->id,
            'event_type' => 'staff_only_event',
            'to_status' => CivicReportStatus::InProgress,
            'reason' => 'Hidden event details for staff only.',
            'citizen_visible' => false,
            'occurred_at' => now()->subMinutes(30),
        ]);
        ReportComment::factory()->create([
            'civic_report_id' => $report->id,
            'visibility' => ReportCommentVisibility::Public,
            'body' => 'The resident added a public clarification.',
            'created_at' => now()->subMinutes(20),
        ]);
        ReportComment::factory()->create([
            'civic_report_id' => $report->id,
            'visibility' => ReportCommentVisibility::Internal,
            'body' => 'Confidential internal staff note.',
            'created_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($citizen)
            ->get(route('citizen.reports.show', $report))
            ->assertOk()
            ->assertSee('Progress timeline')
            ->assertSee('The service team began reviewing this report.')
            ->assertSee('The resident added a public clarification.')
            ->assertDontSee('Hidden event details for staff only.')
            ->assertDontSee('Confidential internal staff note.');
    }

    public function test_guest_report_timeline_hides_internal_history_after_tracking_access(): void
    {
        $this->seed();
        $report = CivicReport::factory()->create(['user_id' => null]);
        ReportStatusEvent::factory()->create([
            'civic_report_id' => $report->id,
            'event_type' => 'private_event',
            'reason' => 'Guest must not see this event.',
            'citizen_visible' => false,
        ]);
        ReportComment::factory()->create([
            'civic_report_id' => $report->id,
            'visibility' => ReportCommentVisibility::Internal,
            'body' => 'Guest must not see this note.',
        ]);

        $this->withSession(['guest_report_access_'.$report->id => true])
            ->get(route('public.reports.track.show', $report))
            ->assertOk()
            ->assertSee('Progress timeline')
            ->assertDontSee('Guest must not see this event.')
            ->assertDontSee('Guest must not see this note.');
    }

    public function test_category_picker_uses_public_catalog_descriptions_and_availability(): void
    {
        $this->seed();
        ServiceCategory::factory()->create([
            'name' => 'Inactive service category',
            'description' => 'This category is not available to reporters.',
            'is_active' => false,
            'is_public' => true,
        ]);

        $this->get(route('public.reports.create'))
            ->assertOk()
            ->assertSee('type="radio"', false)
            ->assertSee('Road surface damage, sidewalks, crossings, and signs.')
            ->assertSee('Location required')
            ->assertDontSee('Inactive service category');
    }

    public function test_citizen_report_previews_show_location_labels_and_fallbacks(): void
    {
        $this->seed();
        $citizen = $this->citizen();
        $locatedReport = CivicReport::factory()->create(['user_id' => $citizen->id]);
        ReportLocation::factory()->create([
            'civic_report_id' => $locatedReport->id,
            'latitude' => 34.5553,
            'longitude' => 69.2075,
            'address' => 'Central Library entrance',
        ]);
        CivicReport::factory()->create(['user_id' => $citizen->id]);

        $this->actingAs($citizen)
            ->get(route('citizen.reports.index'))
            ->assertOk()
            ->assertSee('Central Library entrance')
            ->assertSee('Location not included')
            ->assertSee('data-location-preview', false);
    }

    public function test_staff_queue_presets_keep_reports_scoped_to_active_departments(): void
    {
        $this->seed();
        $unit = OrganizationUnit::factory()->create();
        $otherUnit = OrganizationUnit::factory()->create();
        $staff = $this->staffMemberOf($unit);
        $urgentReport = CivicReport::factory()->create([
            'routed_unit_id' => $unit->id,
            'status' => CivicReportStatus::InProgress,
            'priority' => CivicReportPriority::Urgent,
        ]);
        $otherUrgentReport = CivicReport::factory()->create([
            'routed_unit_id' => $otherUnit->id,
            'status' => CivicReportStatus::Submitted,
            'priority' => CivicReportPriority::Urgent,
        ]);
        $overdueReport = CivicReport::factory()->create([
            'routed_unit_id' => $unit->id,
            'status' => CivicReportStatus::Acknowledged,
        ]);
        ReportSlaInstance::factory()->create([
            'civic_report_id' => $overdueReport->id,
            'first_response_due_at' => now()->subMinute(),
            'resolution_due_at' => now()->addDay(),
        ]);
        $otherOverdueReport = CivicReport::factory()->create([
            'routed_unit_id' => $otherUnit->id,
            'status' => CivicReportStatus::InProgress,
        ]);
        ReportSlaInstance::factory()->create([
            'civic_report_id' => $otherOverdueReport->id,
            'first_response_due_at' => now()->subMinute(),
            'resolution_due_at' => now()->addDay(),
        ]);

        $this->actingAs($staff)
            ->get(route('staff.reports.index', ['preset' => 'urgent']))
            ->assertOk()
            ->assertSee($urgentReport->public_reference)
            ->assertDontSee($otherUrgentReport->public_reference);

        $this->actingAs($staff)
            ->get(route('staff.reports.index', ['preset' => 'overdue']))
            ->assertOk()
            ->assertSee($overdueReport->public_reference)
            ->assertDontSee($otherOverdueReport->public_reference);

        $this->actingAs($staff)
            ->get(route('staff.reports.index', ['preset' => 'unsupported']))
            ->assertRedirect()
            ->assertSessionHasErrors('preset');
    }

    public function test_worker_summary_bucket_lists_only_the_workers_active_assignments(): void
    {
        $this->seed();
        $worker = User::factory()->create();
        $worker->assignRole(RoleName::Worker->value);
        $otherWorker = User::factory()->create();
        $otherWorker->assignRole(RoleName::Worker->value);
        $submittedTask = WorkTask::factory()->create([
            'work_order_id' => WorkOrder::factory(),
            'title' => 'Submitted evidence task',
        ]);
        $assignedTask = WorkTask::factory()->create([
            'work_order_id' => WorkOrder::factory(),
            'title' => 'Assigned response task',
        ]);
        $otherTask = WorkTask::factory()->create([
            'work_order_id' => WorkOrder::factory(),
            'title' => 'Other worker task',
        ]);
        TaskAssignment::factory()->create([
            'work_task_id' => $submittedTask->id,
            'worker_id' => $worker->id,
            'status' => TaskAssignmentStatus::Submitted,
        ]);
        TaskAssignment::factory()->create([
            'work_task_id' => $assignedTask->id,
            'worker_id' => $worker->id,
            'status' => TaskAssignmentStatus::Assigned,
        ]);
        TaskAssignment::factory()->create([
            'work_task_id' => $otherTask->id,
            'worker_id' => $otherWorker->id,
            'status' => TaskAssignmentStatus::Submitted,
        ]);

        $this->actingAs($worker)
            ->get(route('worker.tasks.index', ['bucket' => 'awaiting_verification']))
            ->assertOk()
            ->assertSee($submittedTask->title)
            ->assertDontSee($assignedTask->title)
            ->assertDontSee($otherTask->title);
    }

    public function test_citizen_cannot_open_another_reporters_detail_page(): void
    {
        $this->seed();
        $citizen = $this->citizen();
        $otherReport = CivicReport::factory()->create();

        $this->actingAs($citizen)
            ->get(route('citizen.reports.show', $otherReport))
            ->assertNotFound();
    }

    private function citizen(): User
    {
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);
        $citizen->markEmailAsVerified();

        return $citizen;
    }

    private function staffMemberOf(OrganizationUnit $unit): User
    {
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        $staff->markEmailAsVerified();
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => RoleName::Staff->value,
            'status' => WorkforceMembershipStatus::Active->value,
            'ended_at' => null,
        ]);

        return $staff;
    }
}
