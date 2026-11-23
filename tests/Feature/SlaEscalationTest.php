<?php

namespace Tests\Feature;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use App\Enums\ReportSlaState;
use App\Enums\RoleName;
use App\Jobs\EscalateReportSla;
use App\Models\CivicReport;
use App\Models\ReportSlaInstance;
use App\Models\ReportSlaPolicy;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Notifications\SlaEscalationNotification;
use App\Services\ReportSlaService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SlaEscalationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_create_and_version_sla_policies(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $category = ServiceCategory::query()->where('code', 'ROAD')->firstOrFail();

        $this->actingAs($administrator)
            ->post(route('admin.sla-policies.store'), [
                'name' => 'Roads response policy',
                'service_category_id' => $category->id,
                'organization_unit_id' => null,
                'priority' => CivicReportPriority::Normal->value,
                'first_response_minutes' => '60',
                'resolution_minutes' => '1440',
                'escalation_repeat_minutes' => '30',
                'is_active' => '1',
                'effective_at' => null,
                'expires_at' => null,
            ])
            ->assertRedirect(route('admin.sla-policies.index'));

        $policy = ReportSlaPolicy::query()->where('name', 'Roads response policy')->firstOrFail();
        $this->assertSame(1, $policy->version);
        $this->assertSame($category->id, $policy->service_category_id);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $administrator->id,
            'event_key' => 'report_sla.policy_created',
            'subject_id' => $policy->id,
        ]);
        $this->actingAs($administrator)
            ->get(route('admin.sla-policies.index', ['q' => 'Roads response', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Roads response policy');

        $this->actingAs($administrator)
            ->put(route('admin.sla-policies.update', $policy), [
                'name' => 'Roads response policy',
                'service_category_id' => $category->id,
                'organization_unit_id' => null,
                'priority' => CivicReportPriority::Normal->value,
                'first_response_minutes' => '90',
                'resolution_minutes' => '1440',
                'escalation_repeat_minutes' => '30',
                'is_active' => '1',
                'effective_at' => null,
                'expires_at' => null,
            ])
            ->assertRedirect(route('admin.sla-policies.index'));

        $this->assertSame(2, $policy->fresh()->version);
        $this->assertSame(90, $policy->fresh()->first_response_minutes);
    }

    public function test_submission_uses_the_most_specific_policy_and_snapshots_deadlines(): void
    {
        $this->seed();
        $category = ServiceCategory::query()->where('code', 'ROAD')->firstOrFail();
        ReportSlaPolicy::factory()->create([
            'service_category_id' => null,
            'organization_unit_id' => null,
            'priority' => null,
            'name' => 'General policy',
            'first_response_minutes' => 480,
            'resolution_minutes' => 5760,
        ]);
        $specificPolicy = ReportSlaPolicy::factory()->create([
            'service_category_id' => $category->id,
            'organization_unit_id' => null,
            'priority' => CivicReportPriority::Normal->value,
            'name' => 'Roads standard policy',
            'first_response_minutes' => 60,
            'resolution_minutes' => 1440,
        ]);
        $submittedAt = now();

        $this->post(route('public.reports.store'), [
            'service_category_id' => $category->id,
            'title' => 'Damaged curb ramp',
            'description' => 'The curb ramp surface is broken.',
            'latitude' => '38.8977',
            'longitude' => '-77.0365',
        ])->assertSessionHasNoErrors();

        $report = CivicReport::query()->firstOrFail();
        $instance = $report->slaInstance()->firstOrFail();
        $this->assertSame($specificPolicy->id, $instance->report_sla_policy_id);
        $this->assertSame('Roads standard policy', $instance->policy_name_snapshot);
        $this->assertSame($specificPolicy->version, $instance->policy_version_snapshot);
        $this->assertSame(
            $submittedAt->copy()->addMinutes(60)->format('Y-m-d H:i:s'),
            $instance->first_response_due_at->format('Y-m-d H:i:s'),
        );
        $this->assertSame(
            $submittedAt->copy()->addMinutes(1440)->format('Y-m-d H:i:s'),
            $instance->resolution_due_at->format('Y-m-d H:i:s'),
        );

        $specificPolicy->forceFill(['version' => 2, 'first_response_minutes' => 90])->save();
        $this->assertSame(1, $instance->fresh()->policy_version_snapshot);
        $this->assertSame(60, $instance->fresh()->first_response_target_minutes);

        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $this->actingAs($administrator)
            ->put(route('staff.reports.triage', $report), [
                'status' => CivicReportStatus::UnderReview->value,
                'priority' => CivicReportPriority::Normal->value,
                'is_public' => '0',
            ])
            ->assertRedirect(route('staff.reports.show', $report));

        $this->assertNotNull($instance->fresh()->first_response_at);
        $this->assertNull($instance->fresh()->first_response_breached_at);
        $this->assertDatabaseHas('sla_events', [
            'report_sla_instance_id' => $instance->id,
            'event_type' => 'first_response_met',
        ]);
    }

    public function test_reports_without_a_matching_policy_do_not_receive_fake_sla_targets(): void
    {
        $this->seed();
        $category = ServiceCategory::query()->where('code', 'ROAD')->firstOrFail();
        $otherCategory = ServiceCategory::factory()->create();
        ReportSlaPolicy::factory()->create([
            'service_category_id' => $otherCategory->id,
            'organization_unit_id' => null,
            'priority' => null,
        ]);

        $this->post(route('public.reports.store'), [
            'service_category_id' => $category->id,
            'title' => 'Street obstruction',
            'description' => 'A fallen branch blocks a footpath.',
            'latitude' => '38.8977',
            'longitude' => '-77.0365',
        ])->assertSessionHasNoErrors();

        $report = CivicReport::query()->firstOrFail();
        $this->assertDatabaseCount('report_sla_instances', 1);
        $this->assertSame(ReportSlaState::NotApplicable, $report->slaInstance->state);
        $this->assertNull($report->slaInstance->first_response_due_at);
    }

    public function test_scheduler_queues_only_reports_with_overdue_targets(): void
    {
        $this->seed();
        Bus::fake();
        $overdue = ReportSlaInstance::factory()->create([
            'first_response_due_at' => now()->subMinute(),
            'resolution_due_at' => now()->addHours(2),
            'next_escalation_at' => now()->subMinute(),
        ]);
        ReportSlaInstance::factory()->create([
            'first_response_due_at' => now()->addHour(),
            'resolution_due_at' => now()->addHours(4),
            'next_escalation_at' => now()->addHour(),
        ]);

        $this->artisan('civictrack:sla:check')->assertExitCode(0);

        Bus::assertDispatched(EscalateReportSla::class, fn (EscalateReportSla $job): bool => $job->reportSlaInstanceId === $overdue->id);
        Bus::assertDispatchedTimes(EscalateReportSla::class, 1);
    }

    public function test_sla_escalation_is_audited_and_notified_only_once(): void
    {
        $this->seed();
        Notification::fake();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $instance = ReportSlaInstance::factory()->create([
            'first_response_due_at' => now()->subMinutes(5),
            'resolution_due_at' => now()->addHours(2),
            'next_escalation_at' => now()->subMinute(),
        ]);

        $job = new EscalateReportSla($instance->id);
        $job->handle(app(ReportSlaService::class));
        $job->handle(app(ReportSlaService::class));

        $this->assertSame(ReportSlaState::Breached, $instance->fresh()->state);
        $this->assertSame(1, $instance->fresh()->escalation_count);
        $this->assertDatabaseHas('sla_events', [
            'report_sla_instance_id' => $instance->id,
            'event_type' => 'escalation_sent',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'event_key' => 'report_sla.escalated',
            'subject_id' => $instance->id,
        ]);
        Notification::assertSentToTimes($administrator, SlaEscalationNotification::class, 1);
    }
}
