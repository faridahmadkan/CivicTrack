<?php

namespace Database\Factories;

use App\Enums\OrganizationUnitType;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationUnit>
 */
class OrganizationUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'created_by' => null,
            'code' => fake()->unique()->bothify('UNIT-??##'),
            'name' => fake()->unique()->company().' Services',
            'unit_type' => OrganizationUnitType::Department,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
