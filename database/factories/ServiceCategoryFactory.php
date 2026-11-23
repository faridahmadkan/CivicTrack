<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('CAT????')),
            'name' => $this->faker->unique()->words(2, true),
            'slug' => Str::slug($this->faker->unique()->words(2, true).'-'.$this->faker->unique()->lexify('????')),
            'description' => $this->faker->sentence(),
            'requires_location' => true,
            'is_public' => true,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
