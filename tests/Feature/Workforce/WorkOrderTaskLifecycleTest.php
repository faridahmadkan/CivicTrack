<?php

namespace Tests\Feature\Workforce;

use App\Enums\CivicReportStatus;
use App\Enums\RoleName;
use App\Enums\TaskAssignmentStatus;
use App\Enums\WorkTaskStatus;
use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\TaskAssignment;
use App\Models\TaskDependency;
use App\Models\TaskEvidence;
use App\Models\TaskVerification;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkOrderTaskLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_multi_task_work_order_requires_worker_evidence_and_staff_verification_before_resolution(): void
    {
        Storage::fake('local');
        $this->seed();
        [$report, $staff, $unit] = $this->createAcknowledgedReportAndStaff();

        $this->actingAs($staff)
            ->post(route('staff.reports.work-orders.store', $report), [
                'title' => 'Repair damaged crossing sign',
                'instructions' => 'Follow traffic control policy and document the work.',
                'tasks' => [
                    [
                        'title' => 'Inspect and secure the site',
                        'instructions' => 'Confirm the sign base and place a safety marker.',
                        'priority' => 'high',
                    ],
                    [
                        'title' => 'Replace the crossing sign',
                        'instructions' => 'Install the approved sign and take completion photos.',
                        'priority' => 'normal',
                        'depends_on' => ['0'],
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $workOrder = WorkOrder::query()->firstOrFail();
        $tasks = $workOrder->tasks()->orderBy('sequence')->get();
        $firstTask = $tasks[0];
        $secondTask = $tasks[1];
        $this->assertCount(2, $tasks);
        $this->assertDatabaseHas('task_dependencies', [
            'work_task_id' => $secondTask->id,
            'depends_on_task_id' => $firstTask->id,
        ]);
        $this->assertSame(CivicReportStatus::Acknowledged, $report->fresh()->status);

        $worker = User::factory()->create();
        $worker->assignRole(RoleName::Worker->value);
        $worker->markEmailAsVerified();
        WorkerProfile::factory()->create(['user_id' => $worker->id]);
        UnitMembership::factory()->create([
            'user_id' => $worker->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => WorkforceApplicationType::Worker,
            'status' => WorkforceMembershipStatus::Active,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.tasks.assign', $firstTask), [
                'worker_id' => $worker->id,
                'assignment_instructions' => 'Use the marked safety kit.',
            ])
            ->assertSessionHasNoErrors();

        $assignment = TaskAssignment::query()->where('work_task_id', $firstTask->id)->firstOrFail();
        $this->assertSame(TaskAssignmentStatus::Assigned, $assignment->status);
        $this->assertSame(CivicReportStatus::InProgress, $report->fresh()->status);

        $this->actingAs($worker)
            ->post(route('worker.tasks.accept', $firstTask))
            ->assertSessionHasNoErrors();
        $this->post(route('worker.tasks.start', $firstTask))->assertSessionHasNoErrors();
        $this->post(route('worker.tasks.evidence.store', $firstTask), [
            'evidence_type' => 'before',
            'caption' => 'Site condition before repair.',
            'evidence' => [UploadedFile::fake()->image('site-before.jpg')],
        ])->assertSessionHasNoErrors();

        Storage::disk('local')->assertExists(TaskEvidence::query()->firstOrFail()->storage_path);

        $this->post(route('worker.tasks.submit', $firstTask))->assertSessionHasNoErrors();
        $this->assertSame(WorkTaskStatus::SubmittedForReview, $firstTask->fresh()->status);

        $this->actingAs($staff)
            ->put(route('staff.tasks.verify', $firstTask), ['decision' => 'approved'])
            ->assertSessionHasNoErrors();

        $this->assertSame(WorkTaskStatus::Completed, $firstTask->fresh()->status);
        $this->assertSame(CivicReportStatus::InProgress, $report->fresh()->status);
        $this->assertDatabaseCount('task_verifications', 1);

        $this->actingAs($staff)
            ->post(route('staff.tasks.assign', $secondTask), ['worker_id' => $worker->id])
            ->assertSessionHasNoErrors();
        $this->assertSame(TaskAssignmentStatus::Assigned, $secondTask->fresh()->activeAssignment->status);

        $this->actingAs($worker)
            ->post(route('worker.tasks.accept', $secondTask))
            ->assertSessionHasNoErrors();
        $this->post(route('worker.tasks.start', $secondTask))->assertSessionHasNoErrors();
        $this->post(route('worker.tasks.evidence.store', $secondTask), [
            'evidence_type' => 'after',
            'caption' => 'Replacement sign is installed.',
            'evidence' => [UploadedFile::fake()->image('site-after.jpg')],
        ])->assertSessionHasNoErrors();
        $this->post(route('worker.tasks.submit', $secondTask))->assertSessionHasNoErrors();

        $this->actingAs($staff)
            ->put(route('staff.tasks.verify', $secondTask), ['decision' => 'approved'])
            ->assertSessionHasNoErrors();

        $this->assertSame(WorkTaskStatus::Completed, $secondTask->fresh()->status);
        $this->assertSame('completed', $workOrder->fresh()->status->value);
        $this->assertSame(CivicReportStatus::Resolved, $report->fresh()->status);
        $this->assertNotNull($report->fresh()->resolved_at);
        $this->assertDatabaseHas('report_status_events', [
            'civic_report_id' => $report->id,
            'to_status' => CivicReportStatus::Resolved->value,
            'event_type' => 'work_verified',
        ]);
    }

    public function test_cyclic_task_dependencies_are_rejected_before_a_work_order_is_created(): void
    {
        $this->seed();
        [$report, $staff] = $this->createAcknowledgedReportAndStaff();

        $this->actingAs($staff)
            ->from(route('staff.work-orders.create', $report))
            ->post(route('staff.reports.work-orders.store', $report), [
                'title' => 'Cyclic plan',
                'tasks' => [
                    ['title' => 'First task step', 'priority' => 'normal', 'depends_on' => ['1']],
                    ['title' => 'Second task step', 'priority' => 'normal', 'depends_on' => ['0']],
                ],
            ])
            ->assertRedirect(route('staff.work-orders.create', $report))
            ->assertSessionHasErrors('tasks');

        $this->assertDatabaseCount('work_orders', 0);
        $this->assertSame(CivicReportStatus::Acknowledged, $report->fresh()->status);
    }

    public function test_worker_from_another_unit_cannot_be_assigned_to_the_task(): void
    {
        $this->seed();
        [$report, $staff, $unit] = $this->createAcknowledgedReportAndStaff();
        $workOrder = WorkOrder::factory()->create([
            'civic_report_id' => $report->id,
            'organization_unit_id' => $unit->id,
        ]);
        $task = WorkTask::factory()->create(['work_order_id' => $workOrder->id]);
        $otherUnit = OrganizationUnit::factory()->create();
        $worker = User::factory()->create();
        $worker->assignRole(RoleName::Worker->value);
        WorkerProfile::factory()->create(['user_id' => $worker->id]);
        UnitMembership::factory()->create([
            'user_id' => $worker->id,
            'organization_unit_id' => $otherUnit->id,
            'membership_type' => WorkforceApplicationType::Worker,
            'status' => WorkforceMembershipStatus::Active,
        ]);

        $this->actingAs($staff)
            ->from(route('staff.work-orders.show', $workOrder))
            ->post(route('staff.tasks.assign', $task), ['worker_id' => $worker->id])
            ->assertRedirect(route('staff.work-orders.show', $workOrder))
            ->assertSessionHasErrors('worker_id');

        $this->assertDatabaseCount('task_assignments', 0);
        $this->assertSame(WorkTaskStatus::Queued, $task->fresh()->status);
    }

    /** @return array{CivicReport, User, OrganizationUnit} */
    private function createAcknowledgedReportAndStaff(): array
    {
        $unit = OrganizationUnit::factory()->create();
        $report = CivicReport::factory()->create([
            'routed_unit_id' => $unit->id,
            'status' => CivicReportStatus::Acknowledged,
        ]);
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        $staff->markEmailAsVerified();
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => WorkforceApplicationType::Staff,
            'status' => WorkforceMembershipStatus::Active,
        ]);

        return [$report, $staff, $unit];
    }
}
