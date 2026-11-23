<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleName;
use App\Models\AuditEvent;
use App\Models\RegistrationControl;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RegistrationControlTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_close_citizen_registration_and_the_public_form_is_enforced(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($administrator)->put(route('admin.registration-controls.update', 'citizen'), [
            'context' => 'citizen',
            'enabled' => '0',
            'opens_at' => null,
            'closes_at' => null,
        ])->assertRedirect(route('admin.registration-controls.index'));

        $control = RegistrationControl::query()->where('context', 'citizen')->firstOrFail();
        $this->assertFalse($control->enabled);
        $this->assertSame($administrator->id, $control->changed_by);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $administrator->id,
            'event_key' => 'registration_control.updated',
            'properties->context' => 'citizen',
            'properties->enabled' => false,
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Citizen registration is currently closed')
            ->assertDontSee(route('register'));

        $this->post(route('logout'));
        $this->post(route('register'), [
            'name' => 'Amina Rahimi',
            'email' => 'amina@example.test',
            'password' => 'A secure citizen password 2026!',
            'password_confirmation' => 'A secure citizen password 2026!',
            'privacy_acknowledged' => '1',
        ])->assertSessionHasErrors('registration');

        $this->assertDatabaseMissing('users', ['email' => 'amina@example.test']);
        $this->assertSame(1, AuditEvent::query()->count());
    }

    public function test_registration_schedule_is_applied_even_when_the_enabled_switch_is_on(): void
    {
        $this->seed();
        $control = RegistrationControl::query()->where('context', 'staff')->firstOrFail();
        $control->update([
            'enabled' => true,
            'opens_at' => now()->addHour(),
            'closes_at' => now()->addDays(2),
        ]);

        $this->assertFalse($control->fresh()->isOpenAt(now()));
        $control->update(['opens_at' => now()->subHour()]);
        $this->assertTrue($control->fresh()->isOpenAt(now()));
        $control->update(['closes_at' => now()]);
        $this->assertFalse($control->fresh()->isOpenAt(now()));
    }

    public function test_non_administrator_cannot_change_registration_controls(): void
    {
        $this->seed();
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);

        $this->actingAs($citizen)
            ->put(route('admin.registration-controls.update', 'worker'), [
                'context' => 'worker',
                'enabled' => '1',
            ])
            ->assertForbidden();

        $this->assertFalse(RegistrationControl::query()->where('context', 'worker')->value('enabled'));
        $this->assertDatabaseCount('audit_events', 0);
    }
}
