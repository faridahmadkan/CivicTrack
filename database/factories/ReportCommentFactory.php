<?php

namespace Database\Factories;

use App\Models\ReportComment;
use App\Models\CivicReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportComment>
 */
class ReportCommentFactory extends Factory
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
            'visibility' => 'public',
            'body' => $this->faker->paragraph(),
            'created_at' => now(),
        ];
    }
}
