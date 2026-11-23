<?php

namespace Database\Factories;

use App\Models\WorkerProfile;
use App\Models\User;
use App\Models\WorkforceApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkerProfile>
 */
class WorkerProfileFactory extends Factory
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
            'approved_application_id' => null,
            'approved_by' => null,
            'position_title' => fake()->jobTitle(),
            'experience_summary' => fake()->paragraph(),
            'qualification_summary' => fake()->sentence(),
            'approved_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (WorkerProfile $profile): void {
            if ($profile->approved_application_id === null) {
                $application = WorkforceApplication::factory()->worker()->create([
                    'user_id' => $profile->user_id,
                    'status' => 'approved',
                ]);

                $profile->approved_application_id = $application->id;
            }
        });
    }
}
