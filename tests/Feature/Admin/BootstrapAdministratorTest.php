<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BootstrapAdministratorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_bootstrap_creates_a_verified_administrator_from_runtime_configuration(): void
    {
        $this->seed();
        config([
            'civictrack.bootstrap_admin.name' => 'CivicTrack Administrator',
            'civictrack.bootstrap_admin.email' => 'platform-admin@example.test',
            'civictrack.bootstrap_admin.password' => 'OnlyForThisTest-Admin-2026!',
        ]);

        $this->artisan('civictrack:bootstrap-admin')
            ->expectsOutput('The initial administrator account was created. Remove the bootstrap password from the environment configuration.')
            ->assertSuccessful();

        $administrator = User::query()->where('email', 'platform-admin@example.test')->firstOrFail();

        $this->assertSame(AccountStatus::Active, $administrator->account_status);
        $this->assertNotNull($administrator->email_verified_at);
        $this->assertTrue($administrator->hasRole(RoleName::Administrator->value));
        $this->assertTrue(Hash::check('OnlyForThisTest-Admin-2026!', $administrator->password));
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $administrator->id,
            'event_key' => 'administrator.bootstrap_created',
            'subject_id' => $administrator->id,
        ]);
    }

    public function test_repeated_bootstrap_does_not_change_the_existing_administrator(): void
    {
        $this->seed();
        config([
            'civictrack.bootstrap_admin.name' => 'CivicTrack Administrator',
            'civictrack.bootstrap_admin.email' => 'platform-admin@example.test',
            'civictrack.bootstrap_admin.password' => 'First-Administrator-Password-2026!',
        ]);

        $this->artisan('civictrack:bootstrap-admin')->assertSuccessful();
        $administrator = User::query()->where('email', 'platform-admin@example.test')->firstOrFail();
        $originalId = $administrator->id;
        $originalPasswordHash = $administrator->password;
        $originalAuditCount = AuditEvent::query()->count();

        config(['civictrack.bootstrap_admin.password' => 'A-Different-Administrator-Password-2026!']);

        $this->artisan('civictrack:bootstrap-admin')
            ->expectsOutput('An account with the configured administrator identity already exists; no changes were made.')
            ->assertExitCode(1);

        $administrator->refresh();

        $this->assertSame($originalId, $administrator->id);
        $this->assertSame($originalPasswordHash, $administrator->password);
        $this->assertSame($originalAuditCount, AuditEvent::query()->count());
        $this->assertTrue(Hash::check('First-Administrator-Password-2026!', $administrator->password));
    }

    public function test_bootstrap_refuses_missing_or_weak_configuration(): void
    {
        config([
            'civictrack.bootstrap_admin.name' => 'CivicTrack Administrator',
            'civictrack.bootstrap_admin.email' => 'platform-admin@example.test',
            'civictrack.bootstrap_admin.password' => 'short',
        ]);

        $this->artisan('civictrack:bootstrap-admin')
            ->expectsOutput('Bootstrap configuration is incomplete or does not meet the minimum security requirements.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('audit_events', 0);
    }
}
