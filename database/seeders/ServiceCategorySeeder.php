<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['ROAD', 'Roads and sidewalks', 'Road surface damage, sidewalks, crossings, and signs.'],
            ['LIGHT', 'Street lighting', 'Street lights that are damaged, out, or unsafe.'],
            ['WASTE', 'Waste and sanitation', 'Missed collection, illegal dumping, and public cleanliness.'],
            ['WATER', 'Water and drainage', 'Water leaks, blocked drains, and flooding concerns.'],
            ['PARK', 'Parks and public spaces', 'Maintenance and safety concerns in public spaces.'],
            ['FACILITY', 'Public facilities', 'Maintenance concerns at public buildings and facilities.'],
        ] as [$code, $name, $description]) {
            ServiceCategory::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'slug' => Str::slug($name.'-'.$code),
                    'description' => $description,
                    'requires_location' => true,
                    'is_public' => true,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }
    }
}
