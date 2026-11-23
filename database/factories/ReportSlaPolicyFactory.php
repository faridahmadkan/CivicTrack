<?php

namespace Database\Factories;

use App\Enums\CivicReportPriority;
use App\Models\OrganizationUnit;
use App\Models\ServiceCategory;
use App\Models\ReportSlaPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportSlaPolicy>
 */
class ReportSlaPolicyFactory extends Factory
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
            'organization_unit_id' => OrganizationUnit::factory(),
            'name' => $this->faker->words(3, true).' service target',
            'priority' => CivicReportPriority::Normal->value,
            'version' => 1,
            'first_response_minutes' => 240,
            'resolution_minutes' => 4320,
            'escalation_repeat_minutes' => 60,
            'is_active' => true,
        ];
    }
}
