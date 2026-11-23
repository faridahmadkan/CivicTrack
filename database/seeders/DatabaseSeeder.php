<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            RegistrationControlSeeder::class,
            ServiceCategorySeeder::class,
        ]);

        if (app()->environment(['local', 'testing']) && config('civictrack.demo.enabled')) {
            $this->call(DemoSeeder::class);
        }
    }
}
