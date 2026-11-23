<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\AuditEvent;
use App\Models\RegistrationControl;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CitizenRegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_email_verification_can_be_disabled_for_citizen_registration(): void
    {
        $this->seed();
        config(['civictrack.require_email_verification' => false]);
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'Amina Rahimi',
            'email' => 'amina@example.test',
            'age' => 29,
            'locality' => 'Karte Seh, Kabul',
            'password' => 'A secure citizen password 2026!',
            'password_confirmation' => 'A secure citizen password 2026!',
            'privacy_acknowledged' => '1',
        ])->assertRedirect(route('home'));

        $user = User::query()->where('email', 'amina@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->get(route('citizen.home'))->assertOk();
        Notification::assertNothingSent();
    }

    public function test_open_citizen_registration_creates_a_verified_lifecycle_and_sends_verification(): void
    {
        Notification::fake();
        $this->seed();
        $emailVerificationRequired = (bool) config('civictrack.require_email_verification');

        $response = $this->post(route('register'), [
            'name' => 'Amina Rahimi',
            'email' => 'AMINA@example.test',
            'phone' => '+1 555 010 1234',
            'age' => 29,
            'locality' => 'Karte Seh, Kabul',
            'home_latitude' => '34.5553000',
            'home_longitude' => '69.2075000',
            'password' => 'A secure citizen password 2026!',
            'password_confirmation' => 'A secure citizen password 2026!',
            'privacy_acknowledged' => '1',
        ]);

        $response->assertRedirect(route('home'));

        $user = User::query()->where('email', 'amina@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(AccountStatus::Active, $user->account_status);
        $this->assertTrue($user->hasRole(RoleName::Citizen->value));
        $this->assertSame($emailVerificationRequired, $user->email_verified_at === null);
        $this->assertSame(29, $user->citizenProfile->age);
        $this->assertSame('Karte Seh, Kabul', $user->citizenProfile->locality);
        $this->assertEquals(34.555, (float) $user->citizenProfile->home_latitude);
        $this->assertEquals(69.208, (float) $user->citizenProfile->home_longitude);
        $this->assertArrayNotHasKey('age', $user->citizenProfile->toArray());
        $this->assertDatabaseHas('citizen_profiles', [
            'user_id' => $user->id,
            'phone' => '+1 555 010 1234',
            'age' => 29,
            'locality' => 'Karte Seh, Kabul',
            'home_latitude' => '34.555',
            'home_longitude' => '69.208',
            'privacy_notice_version' => config('civictrack.privacy_notice_version'),
        ]);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $user->id,
            'event_key' => 'citizen.registered',
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);
        $this->assertSame(1, AuditEvent::query()->count());
        if ($emailVerificationRequired) {
            Notification::assertSentTo($user, VerifyEmail::class);
            $this->get(route('home'))->assertRedirect(route('verification.notice'));
        } else {
            Notification::assertNotSentTo($user, VerifyEmail::class);
            $this->get(route('citizen.home'))->assertOk();
        }
    }

    public function test_registration_is_rejected_when_the_database_control_is_closed(): void
    {
        $this->seed();
        RegistrationControl::query()
            ->where('context', RoleName::Citizen->value)
            ->update(['enabled' => false]);

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Amina Rahimi',
            'email' => 'amina@example.test',
            'age' => 29,
            'locality' => 'Karte Seh, Kabul',
            'password' => 'A secure citizen password 2026!',
            'password_confirmation' => 'A secure citizen password 2026!',
            'privacy_acknowledged' => '1',
        ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors('registration');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'amina@example.test']);
        $this->assertDatabaseCount('citizen_profiles', 0);
        $this->assertDatabaseCount('audit_events', 0);
    }

    public function test_administrator_logout_clears_session_before_citizen_registration_in_the_same_browser(): void
    {
        $this->seed();
        $administratorPassword = 'A secure administrator test password!';
        $administrator = User::factory()->create([
            'email' => 'administrator@example.test',
            'password' => Hash::make($administratorPassword),
        ]);
        $administrator->assignRole(RoleName::Administrator->value);

        $this->post(route('login'), [
            'email' => $administrator->email,
            'password' => $administratorPassword,
            'remember' => '1',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($administrator);
        $adminPage = $this->get(route('admin.home'))->assertOk();
        $this->assertStringContainsString('no-store', (string) $adminPage->headers->get('Cache-Control'));

        $this->post(route('logout'))->assertRedirect(route('public.home'));
        $this->assertGuest('web');
        $this->get(route('register'))->assertOk();

        $citizenPassword = 'A secure citizen password 2026!';
        $this->post(route('register'), [
            'name' => 'Amina Rahimi',
            'email' => 'amina@example.test',
            'age' => 29,
            'locality' => 'Karte Seh, Kabul',
            'password' => $citizenPassword,
            'password_confirmation' => $citizenPassword,
            'privacy_acknowledged' => '1',
        ])->assertRedirect(route('home'));

        $citizen = User::query()->where('email', 'amina@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($citizen);
        $this->assertTrue($citizen->hasRole(RoleName::Citizen->value));
        $this->assertFalse($citizen->hasRole(RoleName::Administrator->value));

        $citizen->markEmailAsVerified();

        $this->post(route('logout'))->assertRedirect(route('public.home'));
        $this->assertGuest('web');
        $this->post(route('login'), [
            'email' => $citizen->email,
            'password' => $citizenPassword,
        ])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($citizen);

        $this->get(route('admin.home'))->assertForbidden();
    }
}
