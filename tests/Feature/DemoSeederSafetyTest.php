<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoSeederSafetyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_demo_seeding_is_idempotent_and_does_not_reset_existing_accounts_or_reports(): void
    {
        config([
            'civictrack.demo.enabled' => true,
            'civictrack.demo.password' => 'Environment-only-demo-secret-2026!',
        ]);
        $existingDemoAddressUser = User::factory()->create([
            'name' => 'Existing Demo Address Account',
            'email' => 'demo.citizen.kabul@civictrack.test',
            'password' => 'Existing-password-must-stay-2026!',
        ]);
        $existingPasswordHash = $existingDemoAddressUser->password;

        $this->seed();
        $report = CivicReport::factory()->create();
        $firstCounts = [
            User::query()->count(),
            OrganizationUnit::query()->count(),
            ServiceCategory::query()->count(),
            CivicReport::query()->count(),
        ];
        $demoWorker = User::query()->where('email', 'demo.worker.kabul@civictrack.test')->firstOrFail();
        $demoWorkerPasswordHash = $demoWorker->password;

        $this->seed();

        $this->assertSame($firstCounts, [
            User::query()->count(),
            OrganizationUnit::query()->count(),
            ServiceCategory::query()->count(),
            CivicReport::query()->count(),
        ]);
        $this->assertSame($existingPasswordHash, $existingDemoAddressUser->fresh()->password);
        $this->assertFalse($existingDemoAddressUser->fresh()->roles()->exists());
        $this->assertSame($demoWorkerPasswordHash, $demoWorker->fresh()->password);
        $this->assertTrue(Hash::check('Environment-only-demo-secret-2026!', $demoWorker->password));
        $this->assertSame('DEMO Kabul Field Worker', $demoWorker->name);
        $this->assertTrue($demoWorker->hasRole(RoleName::Worker->value));
        $this->assertDatabaseHas('civic_reports', ['id' => $report->id]);
        $this->assertSame(9, OrganizationUnit::query()->count());
    }

    public function test_demo_accounts_are_not_created_without_an_environment_password(): void
    {
        config(['civictrack.demo.enabled' => true, 'civictrack.demo.password' => '']);

        $this->seed();

        $this->assertSame(9, OrganizationUnit::query()->count());
        $this->assertDatabaseMissing('users', ['email' => 'demo.worker.kabul@civictrack.test']);
    }
}
