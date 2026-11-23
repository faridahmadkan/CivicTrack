<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Models\AuditEvent;
use App\Models\OrganizationUnit;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkforceApplication;
use App\Models\WorkforceApplicationEvent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkforceApplicationReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_approval_creates_a_staff_role_profile_and_unit_membership(): void
    {
        $this->seed();
        $administrator = $this->administrator();
        $applicant = User::factory()->create();
        $applicant->assignRole(RoleName::Citizen->value);
        $organizationUnit = OrganizationUnit::factory()->create();
        $application = WorkforceApplication::factory()->staff()->create([
            'user_id' => $applicant->id,
            'requested_unit_id' => $organizationUnit->id,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee('1 application waiting for review')
            ->assertSee('Applicants appear here with Citizen access while review is pending.')
            ->assertSee('Pending Staff application')
            ->assertSee($application->application_number)
            ->assertSee(route('admin.workforce-applications.show', $application));

        $this->actingAs($administrator)
            ->get(route('admin.workforce-applications.show', $application))
            ->assertOk()
            ->assertSee('Approve application');

        $response = $this->actingAs($administrator)->put(route('admin.workforce-applications.review', $application), [
            'status' => WorkforceApplicationStatus::Approved->value,
        ]);

        $response->assertRedirect(route('admin.workforce-applications.show', $application));
        $this->assertSame(WorkforceApplicationStatus::Approved, $application->fresh()->status);
        $this->assertTrue($applicant->fresh()->hasRole(RoleName::Citizen->value));
        $this->assertTrue($applicant->fresh()->hasRole(RoleName::Staff->value));
        $this->assertFalse($applicant->fresh()->hasRole(RoleName::Worker->value));
        $this->assertDatabaseHas('staff_profiles', [
            'user_id' => $applicant->id,
            'approved_application_id' => $application->id,
            'approved_by' => $administrator->id,
        ]);
        $this->assertDatabaseHas('unit_memberships', [
            'user_id' => $applicant->id,
            'organization_unit_id' => $organizationUnit->id,
            'membership_type' => 'staff',
            'status' => 'active',
        ]);
        $this->assertTrue($applicant->fresh()->can('reports.triage.department'));
        $this->assertFalse($applicant->fresh()->can('assignments.update.own'));
        $this->assertDatabaseHas('workforce_application_events', [
            'workforce_application_id' => $application->id,
            'from_status' => WorkforceApplicationStatus::Submitted->value,
            'to_status' => WorkforceApplicationStatus::Approved->value,
        ]);
        $this->assertSame(1, AuditEvent::query()->where('event_key', 'workforce_application.reviewed')->count());
        $this->assertSame(1, UnitMembership::query()->where('user_id', $applicant->id)->count());

        $this->actingAs($administrator)
            ->get(route('admin.workforce-applications.show', $application))
            ->assertOk()
            ->assertSee(route('admin.workforce-applications.index'))
            ->assertSee('aria-current="page"', false);

        $this->actingAs($administrator)
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Review staff and worker applications')
            ->assertSee(route('admin.workforce-applications.index'));
    }

    public function test_admin_approval_creates_a_worker_role_without_staff_permissions(): void
    {
        $this->seed();
        $administrator = $this->administrator();
        $applicant = User::factory()->create();
        $applicant->assignRole(RoleName::Citizen->value);
        $organizationUnit = OrganizationUnit::factory()->create();
        $application = WorkforceApplication::factory()->worker()->create([
            'user_id' => $applicant->id,
            'requested_unit_id' => $organizationUnit->id,
        ]);

        $this->actingAs($administrator)->put(route('admin.workforce-applications.review', $application), [
            'status' => WorkforceApplicationStatus::Approved->value,
        ])->assertRedirect(route('admin.workforce-applications.show', $application));

        $this->assertTrue($applicant->fresh()->hasRole(RoleName::Worker->value));
        $this->assertFalse($applicant->fresh()->hasRole(RoleName::Staff->value));
        $this->assertTrue($applicant->fresh()->can('assignments.update.own'));
        $this->assertFalse($applicant->fresh()->can('reports.triage.department'));
        $this->assertDatabaseHas('worker_profiles', [
            'user_id' => $applicant->id,
            'approved_application_id' => $application->id,
            'approved_by' => $administrator->id,
        ]);
        $this->assertDatabaseHas('unit_memberships', [
            'user_id' => $applicant->id,
            'organization_unit_id' => $organizationUnit->id,
            'membership_type' => 'worker',
        ]);
        $this->assertSame(1, WorkforceApplicationEvent::query()
            ->where('workforce_application_id', $application->id)
            ->where('to_status', WorkforceApplicationStatus::Approved->value)
            ->count());
    }

    public function test_review_requires_a_reason_before_requesting_information(): void
    {
        $this->seed();
        $administrator = $this->administrator();
        $applicant = User::factory()->create();
        $applicant->assignRole(RoleName::Citizen->value);
        $application = WorkforceApplication::factory()->staff()->create(['user_id' => $applicant->id]);

        $this->actingAs($administrator)
            ->put(route('admin.workforce-applications.review', $application), [
                'status' => WorkforceApplicationStatus::InformationRequested->value,
            ])
            ->assertSessionHasErrors('reason');

        $this->assertSame(WorkforceApplicationStatus::Submitted, $application->fresh()->status);
        $this->assertSame(0, WorkforceApplicationEvent::query()->where('workforce_application_id', $application->id)->count());
        $this->assertSame(0, AuditEvent::query()->count());
    }

    public function test_citizen_cannot_review_a_workforce_application(): void
    {
        $this->seed();
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);
        $application = WorkforceApplication::factory()->worker()->create();

        $this->actingAs($citizen)
            ->put(route('admin.workforce-applications.review', $application), [
                'status' => WorkforceApplicationStatus::Approved->value,
            ])
            ->assertForbidden();

        $this->assertSame(WorkforceApplicationStatus::Submitted, $application->fresh()->status);
        $this->assertDatabaseCount('unit_memberships', 0);
    }

    private function administrator(): User
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        return $administrator;
    }
}
