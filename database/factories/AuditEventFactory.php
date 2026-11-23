<?php

namespace Database\Factories;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditEvent>
 */
class AuditEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => null,
            'event_key' => 'test.event.recorded',
            'subject_type' => null,
            'subject_id' => null,
            'properties' => [],
            'ip_address' => null,
            'user_agent' => null,
            'occurred_at' => now(),
        ];
    }
}
