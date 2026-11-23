<?php

namespace Database\Factories;

use App\Models\ReportStatusEvent;
use App\Models\CivicReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportStatusEvent>
 */
class ReportStatusEventFactory extends Factory
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
            'event_type' => 'submitted',
            'to_status' => 'submitted',
            'citizen_visible' => true,
            'properties' => [],
            'occurred_at' => now(),
        ];
    }
}
