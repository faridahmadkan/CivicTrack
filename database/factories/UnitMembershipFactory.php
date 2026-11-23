<?php

namespace Database\Factories;

use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use App\Models\UnitMembership;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitMembership>
 */
class UnitMembershipFactory extends Factory
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
            'organization_unit_id' => OrganizationUnit::factory(),
            'created_by' => null,
            'membership_type' => WorkforceApplicationType::Staff,
            'status' => WorkforceMembershipStatus::Active,
            'position_title' => fake()->jobTitle(),
            'started_at' => now(),
            'ended_at' => null,
        ];
    }
}
