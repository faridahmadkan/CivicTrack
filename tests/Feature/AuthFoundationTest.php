<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\OrganizationUnit;
use App\Models\RegistrationControl;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_homepage_displays_the_available_citizen_account_path(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('A better way to care for the places we share.')
            ->assertSee('From community reports to verified service work.')
            ->assertSee(route('register'))
            ->assertSee('A secure account is the first step.')
            ->assertSee('Create a citizen account');
    }

    public function test_public_homepage_hides_registration_when_administrator_closes_it(): void
    {
        $this->seed();
        RegistrationControl::query()
            ->where('context', RoleName::Citizen->value)
            ->update(['enabled' => false]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Citizen registration is currently closed')
            ->assertDontSee(route('register'));
    }

    public function test_public_homepage_only_links_to_open_workforce_application_paths(): void
    {
        $this->seed();
        RegistrationControl::query()->where('context', RoleName::Staff->value)->update(['enabled' => true]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee(route('workforce.applications.create', 'staff'))
            ->assertDontSee(route('workforce.applications.create', 'worker'))
            ->assertSee('administrator review')
            ->assertSee('Applicants do not receive staff or worker access until an application is approved.');
    }

    public function test_guest_can_open_an_enabled_workforce_application_form(): void
    {
        $this->seed();
        RegistrationControl::query()->where('context', RoleName::Worker->value)->update(['enabled' => true]);
        OrganizationUnit::factory()->create();

        $this->get(route('public.home'))
            ->assertSee(route('workforce.applications.create', 'worker'));

        $this->get(route('workforce.applications.create', 'worker'))
            ->assertOk()
            ->assertSee('Create an account and apply')
            ->assertSee('Create account and submit application')
            ->assertSee('This citizen account lets you sign in and follow the application review.');
    }
}
