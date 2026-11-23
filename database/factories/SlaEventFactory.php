<?php

namespace Database\Factories;

use App\Models\ReportSlaInstance;
use App\Models\SlaEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlaEvent>
 */
class SlaEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_sla_instance_id' => ReportSlaInstance::factory(),
            'event_type' => 'sla_started',
            'details' => 'SLA targets started for this report.',
            'properties' => [],
            'occurred_at' => now(),
        ];
    }
}
