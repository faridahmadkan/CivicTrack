<?php

namespace Database\Factories;

use App\Models\RoutingRule;
use App\Models\OrganizationUnit;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutingRule>
 */
class RoutingRuleFactory extends Factory
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
            'priority' => 100,
            'is_active' => true,
        ];
    }
}
