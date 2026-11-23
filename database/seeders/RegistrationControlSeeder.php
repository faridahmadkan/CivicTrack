<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\RegistrationControl;
use Illuminate\Database\Seeder;

class RegistrationControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            RoleName::Citizen->value => true,
            RoleName::Staff->value => false,
            RoleName::Worker->value => false,
        ] as $context => $enabled) {
            RegistrationControl::query()->firstOrCreate(
                ['context' => $context],
                ['enabled' => $enabled],
            );
        }
    }
}
