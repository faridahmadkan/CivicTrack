<?php

namespace Database\Factories;

use App\Models\GuestAccessToken;
use App\Models\CivicReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestAccessToken>
 */
class GuestAccessTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'civic_report_id' => CivicReport::factory(),
            'token_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'expires_at' => now()->addDays(180),
        ];
    }
}
