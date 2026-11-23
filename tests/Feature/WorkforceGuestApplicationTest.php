<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Models\OrganizationUnit;
use App\Models\RegistrationControl;
use App\Models\User;
use App\Models\WorkforceApplication;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WorkforceGuestApplicationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_worker_applicant_gets_a_citizen_account_and_pending_banner_without_email_verification(): void
    {
        $this->seed();
        config(['civictrack.require_email_verification' => false]);
        RegistrationControl::query()->where('context', RoleName::Worker->value)->update(['enabled' => true]);
        $unit = OrganizationUnit::factory()->create();

        $this->post(route('workforce.applications.register', WorkforceApplicationType::Worker->value), [
            'name' => 'Farzana Ahmadi',
            'email' => 'farzana@example.test',
            'age' => 31,
            'locality' => 'District 4, Kabul',
            'home_latitude' => '34.5553',
            'home_longitude' => '69.2075',
            'password' => 'A secure worker applicant password 2026!',
            'password_confirmation' => 'A secure worker applicant password 2026!',
            'privacy_acknowledged' => '1',
            'position_title' => 'Water system field worker',
            'requested_unit_id' => $unit->id,
            'experience_summary' => 'Maintained neighborhood water systems.',
        ])->assertRedirect(route('home'));

        $user = User::query()->where('email', 'farzana@example.test')->firstOrFail();
        $application = WorkforceApplication::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(AccountStatus::Active, $user->account_status);
        $this->assertTrue($user->hasRole(RoleName::Citizen->value));
        $this->assertFalse($user->hasAnyRole([RoleName::Staff->value, RoleName::Worker->value]));
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(WorkforceApplicationType::Worker, $application->application_type);
        $this->assertSame(WorkforceApplicationStatus::Submitted, $application->status);
        $this->assertEquals(34.555, (float) $user->citizenProfile->home_latitude);
        $this->assertDatabaseCount('unit_memberships', 0);

        $this->get(route('citizen.home'))
            ->assertOk()
            ->assertSee('Your workforce application is awaiting administrator review')
            ->assertSee('You can continue using your citizen account while your application is pending.')
            ->assertSee('Submitted');
        $this->get(route('workforce.applications.show', $application))->assertOk();
        $this->get(route('worker.home'))->assertForbidden();
        $this->get(route('staff.reports.index'))->assertForbidden();
        $this->assertDatabaseHas('audit_events', [
            'event_key' => 'citizen.registered',
            'subject_id' => $user->id,
        ]);
    }

    public function test_guest_staff_applicant_receives_verification_according_to_configuration(): void
    {
        $this->seed();
        $emailVerificationRequired = (bool) config('civictrack.require_email_verification');
        Notification::fake();
        RegistrationControl::query()->where('context', RoleName::Staff->value)->update(['enabled' => true]);
        $unit = OrganizationUnit::factory()->create();

        $this->post(route('workforce.applications.register', WorkforceApplicationType::Staff->value), [
            'name' => 'Omar Stanikzai',
            'email' => 'omar@example.test',
            'age' => 42,
            'locality' => 'Herat',
            'password' => 'A secure staff applicant password 2026!',
            'password_confirmation' => 'A secure staff applicant password 2026!',
            'privacy_acknowledged' => '1',
            'position_title' => 'Community service coordinator',
            'requested_unit_id' => $unit->id,
        ])->assertRedirect(route('home'));

        $user = User::query()->where('email', 'omar@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame($emailVerificationRequired, $user->email_verified_at === null);
        $this->assertTrue($user->hasRole(RoleName::Citizen->value));
        $this->assertFalse($user->hasRole(RoleName::Staff->value));
        $this->assertSame(WorkforceApplicationStatus::Submitted, $user->workforceApplications()->firstOrFail()->status);

        if ($emailVerificationRequired) {
            Notification::assertSentTo($user, VerifyEmail::class);
            $this->get(route('citizen.home'))->assertRedirect(route('verification.notice'));
        } else {
            Notification::assertNotSentTo($user, VerifyEmail::class);
            $this->get(route('citizen.home'))
                ->assertOk()
                ->assertSee('Your workforce application is awaiting administrator review');
        }
    }

    public function test_closed_application_context_cannot_create_an_account(): void
    {
        $this->seed();
        RegistrationControl::query()->where('context', RoleName::Worker->value)->update(['enabled' => false]);
        $unit = OrganizationUnit::factory()->create();

        $this->from(route('workforce.applications.create', WorkforceApplicationType::Worker->value))
            ->post(route('workforce.applications.register', WorkforceApplicationType::Worker->value), [
                'name' => 'Farzana Ahmadi',
                'email' => 'farzana@example.test',
                'age' => 31,
                'locality' => 'District 4, Kabul',
                'password' => 'A secure worker applicant password 2026!',
                'password_confirmation' => 'A secure worker applicant password 2026!',
                'privacy_acknowledged' => '1',
                'position_title' => 'Water system field worker',
                'requested_unit_id' => $unit->id,
            ])
            ->assertRedirect(route('workforce.applications.create', WorkforceApplicationType::Worker->value))
            ->assertSessionHasErrors('registration');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'farzana@example.test']);
        $this->assertDatabaseCount('workforce_applications', 0);
    }
}
