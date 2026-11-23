<?php

namespace Database\Factories;

use App\Models\CitizenProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CitizenProfile>
 */
class CitizenProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => fake()->optional()->numerify('+1-###-###-####'),
            'preferred_locale' => 'en',
            'privacy_notice_version' => '2026-11',
            'privacy_acknowledged_at' => now(),
        ];
    }
}
