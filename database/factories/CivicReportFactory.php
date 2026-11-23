<?php

namespace Database\Factories;

use App\Models\CivicReport;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CivicReport>
 */
class CivicReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_category_id' => ServiceCategory::factory(),
            'public_reference' => 'CT-'.Str::upper((string) Str::ulid()),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'status' => 'submitted',
            'priority' => 'normal',
            'is_public' => false,
            'submitted_at' => now(),
        ];
    }
}
