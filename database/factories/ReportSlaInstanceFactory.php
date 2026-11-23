<?php

namespace Database\Factories;

use App\Enums\ReportSlaState;
use App\Models\CivicReport;
use App\Models\ReportSlaPolicy;
use App\Models\ReportSlaInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportSlaInstance>
 */
class ReportSlaInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'civic_report_id' => CivicReport::factory(),
            'report_sla_policy_id' => ReportSlaPolicy::factory(),
            'policy_name_snapshot' => 'Standard civic service target',
            'policy_version_snapshot' => 1,
            'first_response_target_minutes' => 240,
            'resolution_target_minutes' => 4320,
            'escalation_repeat_minutes' => 60,
            'started_at' => now(),
            'first_response_due_at' => now()->addMinutes(240),
            'resolution_due_at' => now()->addMinutes(4320),
            'next_escalation_at' => now()->addMinutes(240),
            'state' => ReportSlaState::Active,
        ];
    }
}
