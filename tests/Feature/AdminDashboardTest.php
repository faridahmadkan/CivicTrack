<?php

namespace Tests\Feature;

use App\Enums\CivicReportStatus;
use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\ReportReopenRequest;
use App\Models\ReportSlaInstance;
use App\Models\ServiceFeedback;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_command_center_uses_live_workforce_report_and_feedback_data(): void
    {
        $this->seed();
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $administrator->markEmailAsVerified();
        User::factory()->create()->assignRole(RoleName::Staff->value);
        User::factory()->create()->assignRole(RoleName::Worker->value);
        $openReport = CivicReport::factory()->create([
            'status' => CivicReportStatus::InProgress,
        ]);
        ReportSlaInstance::factory()->create([
            'civic_report_id' => $openReport->id,
            'first_response_due_at' => now()->subMinute(),
            'resolution_due_at' => now()->addHours(2),
            'next_escalation_at' => now()->subMinute(),
        ]);
        $resolvedReport = CivicReport::factory()->create([
            'status' => CivicReportStatus::Resolved,
            'resolved_at' => now(),
        ]);
        ServiceFeedback::factory()->create([
            'civic_report_id' => $resolvedReport->id,
            'rating' => 4,
        ]);
        ReportReopenRequest::factory()->create(['civic_report_id' => $resolvedReport->id]);

        $this->actingAs($administrator)
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Active staff')
            ->assertSee('Active workers')
            ->assertSee('Open reports')
            ->assertSee('Overdue service targets')
            ->assertSee('Reopen reviews')
            ->assertSee('aria-current="page"', false)
            ->assertSee(route('staff.reports.index', ['preset' => 'overdue']), false)
            ->assertSee(route('staff.reports.index', ['preset' => 'reopen_requests']), false);
    }
}
