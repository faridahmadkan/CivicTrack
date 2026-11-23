<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CitizenDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_citizen_sees_only_their_own_database_backed_report_summary(): void
    {
        $this->seed();
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);
        $citizen->markEmailAsVerified();
        $ownReport = CivicReport::factory()->create(['user_id' => $citizen->id]);
        CivicReport::factory()->create();

        $this->actingAs($citizen)
            ->get(route('citizen.home'))
            ->assertOk()
            ->assertSee('Reports submitted')
            ->assertSee('Recent reports')
            ->assertSee('aria-current="page"', false)
            ->assertSee($ownReport->public_reference)
            ->assertDontSee(CivicReport::query()->whereNull('user_id')->firstOrFail()->public_reference);
    }

    public function test_staff_account_does_not_enter_the_citizen_dashboard(): void
    {
        $this->seed();
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        $staff->markEmailAsVerified();

        $this->actingAs($staff)
            ->get(route('citizen.home'))
            ->assertForbidden();
    }
}
