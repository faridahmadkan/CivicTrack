<?php

namespace Database\Factories;

use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Models\WorkforceApplication;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkforceApplication>
 */
class WorkforceApplicationFactory extends Factory
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
            'requested_unit_id' => OrganizationUnit::factory(),
            'reviewed_by' => null,
            'application_number' => 'CT-ST-'.Str::upper((string) Str::ulid()),
            'application_type' => WorkforceApplicationType::Staff,
            'status' => WorkforceApplicationStatus::Submitted,
            'position_title' => fake()->jobTitle(),
            'experience_summary' => fake()->paragraph(),
            'qualification_summary' => fake()->sentence(),
            'additional_information' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'requested_information' => null,
            'decision_reason' => null,
        ];
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_type' => WorkforceApplicationType::Staff,
            'application_number' => 'CT-ST-'.Str::upper((string) Str::ulid()),
        ]);
    }

    public function worker(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_type' => WorkforceApplicationType::Worker,
            'application_number' => 'CT-WK-'.Str::upper((string) Str::ulid()),
        ]);
    }
}
