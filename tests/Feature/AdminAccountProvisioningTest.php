<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use App\Models\OrganizationUnit;
use App\Models\RegistrationControl;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminAccountProvisioningTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_provisioning_respects_disabled_email_verification(): void
    {
        $this->seed();
        config(['civictrack.require_email_verification' => false]);
        Notification::fake();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($administrator)
            ->post(route('admin.accounts.store'), [
                'name' => 'Citizen Example',
                'email' => 'citizen@example.test',
                'role' => RoleName::Citizen->value,
            ])
            ->assertRedirect(route('admin.accounts.index'))
            ->assertSessionHas('status', 'Account created. A password setup link was sent; email verification is disabled.');

        $citizen = User::query()->where('email', 'citizen@example.test')->firstOrFail();
        $this->assertNotNull($citizen->email_verified_at);
        Notification::assertSentTo($citizen, ResetPassword::class);
        Notification::assertNotSentTo($citizen, VerifyEmail::class);
    }

    public function test_administrator_can_create_a_worker_when_public_applications_are_closed(): void
    {
        $this->seed();
        config(['civictrack.require_email_verification' => true]);
        Notification::fake();
        RegistrationControl::query()
            ->whereIn('context', [RoleName::Staff->value, RoleName::Worker->value])
            ->update(['enabled' => false]);
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $unit = OrganizationUnit::factory()->create();

        $this->actingAs($administrator)
            ->post(route('admin.accounts.store'), [
                'name' => 'Field Worker Example',
                'email' => 'field.worker@example.test',
                'role' => RoleName::Worker->value,
                'organization_unit_id' => $unit->id,
                'position_title' => 'Road maintenance worker',
            ])
            ->assertRedirect(route('admin.accounts.index'))
            ->assertSessionHasNoErrors();

        $worker = User::query()->where('email', 'field.worker@example.test')->firstOrFail();
        $this->assertTrue($worker->hasRole(RoleName::Worker->value));
        $this->assertNull($worker->email_verified_at);
        $this->assertDatabaseHas('worker_profiles', [
            'user_id' => $worker->id,
            'approved_application_id' => null,
            'approved_by' => $administrator->id,
            'position_title' => 'Road maintenance worker',
        ]);
        $this->assertDatabaseHas('unit_memberships', [
            'user_id' => $worker->id,
            'organization_unit_id' => $unit->id,
            'membership_type' => WorkforceApplicationType::Worker->value,
            'status' => WorkforceMembershipStatus::Active->value,
            'created_by' => $administrator->id,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $administrator->id,
            'event_key' => 'account.admin_provisioned',
            'subject_id' => $worker->id,
        ]);
        $this->actingAs($administrator)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee('Field Worker Example');

        Notification::assertSentTo($worker, VerifyEmail::class);
        Notification::assertSentTo($worker, ResetPassword::class);

        $worker->markEmailAsVerified();
        $this->actingAs($worker)->get(route('worker.home'))->assertOk();
    }

    public function test_non_administrator_cannot_view_or_create_accounts(): void
    {
        $this->seed();
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);

        $this->actingAs($staff)
            ->get(route('admin.accounts.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('admin.accounts.store'), [
                'name' => 'Unauthorized User',
                'email' => 'unauthorized@example.test',
                'role' => RoleName::Citizen->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.test']);
    }

    public function test_administrator_cannot_create_another_administrator_through_regular_account_provisioning(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($administrator)
            ->from(route('admin.accounts.index'))
            ->post(route('admin.accounts.store'), [
                'name' => 'Another Administrator',
                'email' => 'another.admin@example.test',
                'role' => RoleName::Administrator->value,
            ])
            ->assertRedirect(route('admin.accounts.index'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'another.admin@example.test']);
    }
}
