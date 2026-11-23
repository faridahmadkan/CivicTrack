<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Enums\TaskAssignmentStatus;
use App\Enums\WorkTaskStatus;
use App\Models\OrganizationUnit;
use App\Models\TaskAssignment;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkerRatingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_staff_can_rate_a_worker_after_task_verification(): void
    {
        $this->seed();
        [$staff, , $assignment] = $this->completedAssignmentForStaff();

        $this->actingAs($staff)
            ->post(route('staff.tasks.ratings.store', [$assignment->task, $assignment]), [
                'quality_rating' => '5',
                'safety_rating' => '4',
                'professionalism_rating' => '5',
                'comment' => 'Followed the worksite safety plan and left a clean finish.',
            ])
            ->assertRedirect(route('staff.work-orders.show', $assignment->task->workOrder));

        $this->assertDatabaseHas('worker_ratings', [
            'task_assignment_id' => $assignment->id,
            'rated_by' => $staff->id,
            'quality_rating' => 5,
            'safety_rating' => 4,
            'professionalism_rating' => 5,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'event_key' => 'worker_rating.submitted',
            'actor_id' => $staff->id,
        ]);
        $this->actingAs($staff)
            ->get(route('staff.work-orders.show', $assignment->task->workOrder))
            ->assertOk()
            ->assertSee('Worker feedback')
            ->assertSee('Quality 5/5');
    }

    public function test_staff_cannot_rate_an_assignment_outside_their_service_unit(): void
    {
        $this->seed();
        [$staff, $unit, $assignment] = $this->completedAssignmentForStaff();
        $otherUnit = OrganizationUnit::factory()->create();
        UnitMembership::query()
            ->where('user_id', $staff->id)
            ->where('organization_unit_id', $unit->id)
            ->delete();
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $otherUnit->id,
            'membership_type' => RoleName::Staff->value,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.tasks.ratings.store', [$assignment->task, $assignment]), [
                'quality_rating' => '5',
                'safety_rating' => '5',
                'professionalism_rating' => '5',
            ])
            ->assertSessionHasErrors('taskAssignment');

        $this->assertDatabaseCount('worker_ratings', 0);
    }

    /** @return array{User, OrganizationUnit, TaskAssignment} */
    private function completedAssignmentForStaff(): array
    {
        $unit = OrganizationUnit::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        $staff->markEmailAsVerified();
        UnitMembership::factory()->create([
            'user_id' => $staff->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => RoleName::Staff->value,
        ]);
        $workOrder = WorkOrder::factory()->create(['organization_unit_id' => $unit->id]);
        $task = WorkTask::factory()->create([
            'work_order_id' => $workOrder->id,
            'status' => WorkTaskStatus::Completed,
        ]);
        $worker = User::factory()->create();
        $worker->assignRole(RoleName::Worker->value);
        $assignment = TaskAssignment::factory()->create([
            'work_task_id' => $task->id,
            'worker_id' => $worker->id,
            'status' => TaskAssignmentStatus::Completed,
            'assigned_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
        ]);

        return [$staff, $unit, $assignment];
    }
}
