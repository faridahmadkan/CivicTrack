<?php

namespace Database\Factories;

use App\Enums\WorkOrderStatus;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
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
            'organization_unit_id' => OrganizationUnit::factory(),
            'reference' => 'CT-WO-'.Str::upper((string) Str::ulid()),
            'title' => $this->faker->sentence(5),
            'instructions' => $this->faker->sentence(),
            'status' => WorkOrderStatus::Open,
        ];
    }
}
