<?php

namespace Database\Factories;

use App\Models\RegistrationControl;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationControl>
 */
class RegistrationControlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'context' => fake()->unique()->randomElement(['citizen', 'staff', 'worker']),
            'enabled' => false,
            'opens_at' => null,
            'closes_at' => null,
            'changed_by' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => true,
        ]);
    }
}
