<?php

namespace Tests\Feature;

use App\Enums\ReportAttachmentVisibility;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\ReportAttachment;
use App\Models\TaskAssignment;
use App\Models\TaskEvidence;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateFileAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_access_code_allows_reporter_files_but_not_internal_attachments(): void
    {
        Storage::fake('private');
        $this->seed();
        $report = CivicReport::factory()->create(['user_id' => null]);
        $reporterAttachment = $this->createReportAttachment($report, ReportAttachmentVisibility::Reporter);
        $internalAttachment = $this->createReportAttachment($report, ReportAttachmentVisibility::Internal);
        $token = bin2hex(random_bytes(32));
        $report->guestAccessTokens()->create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDay(),
        ]);

        $this->get(route('public.reports.track.attachments.download', [$report, $reporterAttachment]))
            ->assertNotFound();

        $this->get(route('public.reports.access', $report))->assertOk();
        $sessionId = session()->getId();
        $verificationResponse = $this->post(route('public.reports.verify-access', $report), ['token' => $token]);

        $verificationResponse->assertRedirect(route('public.reports.track.show', $report));
        $this->assertNotSame($sessionId, $verificationResponse->getSession()->getId());
        $this->get(route('public.reports.track.attachments.download', [$report, $reporterAttachment]))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertStreamedContent('private report content');

        $this->get(route('public.reports.track.attachments.download', [$report, $internalAttachment]))
            ->assertNotFound();
    }

    public function test_only_the_report_owner_can_download_reporter_attachments(): void
    {
        Storage::fake('private');
        $this->seed();
        $owner = User::factory()->create();
        $owner->assignRole(RoleName::Citizen->value);
        $report = CivicReport::factory()->create(['user_id' => $owner->id]);
        $reporterAttachment = $this->createReportAttachment($report, ReportAttachmentVisibility::Reporter);
        $internalAttachment = $this->createReportAttachment($report, ReportAttachmentVisibility::Internal);
        $unrelatedCitizen = User::factory()->create();
        $unrelatedCitizen->assignRole(RoleName::Citizen->value);

        $this->actingAs($owner)
            ->get(route('citizen.reports.attachments.download', [$report, $reporterAttachment]))
            ->assertOk()
            ->assertStreamedContent('private report content');

        $this->actingAs($owner)
            ->get(route('citizen.reports.attachments.download', [$report, $internalAttachment]))
            ->assertNotFound();

        $this->actingAs($unrelatedCitizen)
            ->get(route('citizen.reports.attachments.download', [$report, $reporterAttachment]))
            ->assertNotFound();
    }

    public function test_internal_report_attachments_are_limited_to_assigned_staff_and_administrators(): void
    {
        Storage::fake('private');
        $this->seed();
        $unit = OrganizationUnit::factory()->create();
        $report = CivicReport::factory()->create(['routed_unit_id' => $unit->id]);
        $internalAttachment = $this->createReportAttachment($report, ReportAttachmentVisibility::Internal);
        $assignedStaff = $this->createStaffMember($unit);
        $unassignedStaff = User::factory()->create();
        $unassignedStaff->assignRole(RoleName::Staff->value);
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($unassignedStaff)
            ->get(route('staff.reports.attachments.download', [$report, $internalAttachment]))
            ->assertNotFound();

        $this->actingAs($assignedStaff)
            ->get(route('staff.reports.attachments.download', [$report, $internalAttachment]))
            ->assertOk()
            ->assertStreamedContent('private report content');

        $this->actingAs($administrator)
            ->get(route('staff.reports.attachments.download', [$report, $internalAttachment]))
            ->assertOk()
            ->assertStreamedContent('private report content');
    }

    public function test_workers_can_download_only_evidence_for_their_own_active_assignment(): void
    {
        Storage::fake('private');
        $this->seed();
        $unit = OrganizationUnit::factory()->create();
        $workOrder = WorkOrder::factory()->create(['organization_unit_id' => $unit->id]);
        $task = WorkTask::factory()->create(['work_order_id' => $workOrder->id]);
        $worker = User::factory()->create();
        $worker->assignRole(RoleName::Worker->value);
        WorkerProfile::factory()->create(['user_id' => $worker->id]);
        $ownAssignment = TaskAssignment::factory()->create([
            'work_task_id' => $task->id,
            'worker_id' => $worker->id,
        ]);
        $otherWorker = User::factory()->create();
        $otherWorker->assignRole(RoleName::Worker->value);
        $otherAssignment = TaskAssignment::factory()->create([
            'work_task_id' => $task->id,
            'worker_id' => $otherWorker->id,
        ]);
        $ownEvidence = $this->createTaskEvidence($task, $ownAssignment);
        $otherEvidence = $this->createTaskEvidence($task, $otherAssignment);

        $this->actingAs($worker)
            ->get(route('worker.tasks.evidence.download', [$task, $ownEvidence]))
            ->assertOk()
            ->assertStreamedContent('private task evidence');

        $this->actingAs($worker)
            ->get(route('staff.tasks.evidence.download', [$task, $ownEvidence]))
            ->assertForbidden();

        $this->actingAs($worker)
            ->get(route('worker.tasks.evidence.download', [$task, $otherEvidence]))
            ->assertForbidden();

        $this->actingAs($otherWorker)
            ->get(route('worker.tasks.evidence.download', [$task, $ownEvidence]))
            ->assertForbidden();

        $unassignedStaff = User::factory()->create();
        $unassignedStaff->assignRole(RoleName::Staff->value);
        $assignedStaff = $this->createStaffMember($unit);
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($unassignedStaff)
            ->get(route('staff.tasks.evidence.download', [$task, $ownEvidence]))
            ->assertForbidden();

        $this->actingAs($assignedStaff)
            ->get(route('staff.tasks.evidence.download', [$task, $ownEvidence]))
            ->assertOk()
            ->assertStreamedContent('private task evidence');

        $this->actingAs($administrator)
            ->get(route('staff.tasks.evidence.download', [$task, $ownEvidence]))
            ->assertOk()
            ->assertStreamedContent('private task evidence');
    }

    public function test_file_downloads_reject_public_disks_and_paths_outside_the_generated_record_prefix(): void
    {
        Storage::fake('private');
        $this->seed();
        $owner = User::factory()->create();
        $owner->assignRole(RoleName::Citizen->value);
        $report = CivicReport::factory()->create(['user_id' => $owner->id]);
        $attachment = $this->createReportAttachment($report, ReportAttachmentVisibility::Reporter);

        $attachment->forceFill(['storage_disk' => 'public'])->save();
        $this->actingAs($owner)
            ->get(route('citizen.reports.attachments.download', [$report, $attachment]))
            ->assertNotFound();

        $attachment->forceFill([
            'storage_disk' => 'private',
            'storage_path' => 'civic-reports/'.$report->id.'/../../public/secret.pdf',
        ])->save();
        $this->actingAs($owner)
            ->get(route('citizen.reports.attachments.download', [$report, $attachment]))
            ->assertNotFound();
    }

    private function createReportAttachment(
        CivicReport $report,
        ReportAttachmentVisibility $visibility,
    ): ReportAttachment {
        $attachment = ReportAttachment::factory()->create([
            'civic_report_id' => $report->id,
            'storage_disk' => 'private',
            'storage_path' => 'civic-reports/'.$report->id.'/attachment-'.$visibility->value.'.jpg',
            'visibility' => $visibility,
        ]);
        Storage::disk('private')->put($attachment->storage_path, 'private report content');

        return $attachment;
    }

    private function createTaskEvidence(WorkTask $task, TaskAssignment $assignment): TaskEvidence
    {
        $evidence = TaskEvidence::factory()->create([
            'work_task_id' => $task->id,
            'task_assignment_id' => $assignment->id,
            'uploaded_by' => $assignment->worker_id,
            'storage_disk' => 'private',
            'storage_path' => 'task-evidence/'.$task->id.'/assignment-'.$assignment->id.'.jpg',
        ]);
        Storage::disk('private')->put($evidence->storage_path, 'private task evidence');

        return $evidence;
    }

    private function createStaffMember(OrganizationUnit $unit): User
    {
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => WorkforceApplicationType::Staff,
            'status' => WorkforceMembershipStatus::Active,
            'ended_at' => null,
        ]);

        return $staff;
    }
}
