<?php

namespace Database\Factories;

use App\Models\WorkforceApplicationEvent;
use App\Models\WorkforceApplication;
use App\Enums\WorkforceApplicationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkforceApplicationEvent>
 */
class WorkforceApplicationEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workforce_application_id' => WorkforceApplication::factory(),
            'actor_id' => null,
            'event_type' => 'submitted',
            'from_status' => null,
            'to_status' => WorkforceApplicationStatus::Submitted->value,
            'reason' => null,
            'properties' => [],
            'occurred_at' => now(),
        ];
    }
}
