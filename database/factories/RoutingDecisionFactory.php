<?php

namespace Database\Factories;

use App\Models\RoutingDecision;
use App\Models\CivicReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutingDecision>
 */
class RoutingDecisionFactory extends Factory
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
            'source' => 'unassigned_queue',
            'reason' => 'No active routing destination was configured.',
            'decided_at' => now(),
        ];
    }
}
