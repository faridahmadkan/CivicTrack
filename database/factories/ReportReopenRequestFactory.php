<?php

namespace Database\Factories;

use App\Enums\CivicReportStatus;
use App\Enums\ReportReopenStatus;
use App\Models\CivicReport;
use App\Models\ReportReopenRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReportReopenRequest>
 */
class ReportReopenRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'civic_report_id' => CivicReport::factory()->state([
                'status' => CivicReportStatus::Resolved,
                'resolved_at' => now(),
            ]),
            'requester_id' => User::factory(),
            'reviewed_by' => null,
            'request_number' => 'RR-'.Str::upper((string) Str::ulid()),
            'reason' => $this->faker->paragraph(),
            'status' => ReportReopenStatus::Pending,
            'decision_reason' => null,
            'requested_at' => now(),
            'reviewed_at' => null,
        ];
    }
}
