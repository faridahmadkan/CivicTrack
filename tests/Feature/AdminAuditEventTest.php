<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAuditEventTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_filter_and_review_the_append_only_audit_trail(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        AuditEvent::query()->create([
            'actor_id' => $administrator->id,
            'event_key' => 'report_sla.escalated',
            'subject_type' => 'App\\Models\\ReportSlaInstance',
            'subject_id' => 4,
            'properties' => ['escalation_count' => 2, 'targets' => 'resolution'],
            'ip_address' => '192.0.2.12',
            'user_agent' => 'CivicTrack test client',
            'occurred_at' => now(),
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.audit-events.index', [
                'q' => 'ReportSlaInstance',
                'event_key' => 'sla.escalated',
                'actor_id' => $administrator->id,
            ]))
            ->assertOk()
            ->assertSee('report_sla.escalated')
            ->assertSee('resolution')
            ->assertSee('192.0.2.12');
    }

    public function test_staff_cannot_open_the_administrator_audit_trail(): void
    {
        $this->seed();
        $staff = User::factory()->create();
        $staff->assignRole(RoleName::Staff->value);
        $staff->markEmailAsVerified();

        $this->actingAs($staff)
            ->get(route('admin.audit-events.index'))
            ->assertForbidden();
    }
}
