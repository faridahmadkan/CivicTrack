<?php

namespace Database\Factories;

use App\Models\ReportLocation;
use App\Models\CivicReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportLocation>
 */
class ReportLocationFactory extends Factory
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
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'privacy_level' => 'precise_private',
        ];
    }
}
