<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\OrganizationUnitType;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /** Seed fictional Afghan-context operating units and optional local demo accounts. */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || ! config('civictrack.demo.enabled')) {
            return;
        }

        $cities = [
            'Kabul',
            'Herat',
            'Kandahar',
            'Mazar-i-Sharif',
            'Jalalabad',
            'Kunduz',
            'Bamyan',
            'Ghazni',
            'Khost',
        ];
        $units = [];

        foreach ($cities as $city) {
            $code = 'DEMO-'.str($city)->upper()->replace([' ', '-'], '')->value();
            $units[$city] = OrganizationUnit::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => 'DEMO '.$city.' Municipal Services',
                    'unit_type' => OrganizationUnitType::Department,
                    'description' => 'Fictional development-only operating unit for '.$city.'.',
                    'is_active' => true,
                ],
            );
        }

        $password = (string) config('civictrack.demo.password');
        $kabulUnit = $units['Kabul'];

        if (mb_strlen($password) < 16) {
            return;
        }

        foreach ([
            [RoleName::Citizen, null, 'DEMO Kabul Resident', 'demo.citizen.kabul@civictrack.test'],
            [RoleName::Staff, WorkforceApplicationType::Staff, 'DEMO Kabul Staff', 'demo.staff.kabul@civictrack.test'],
            [RoleName::Worker, WorkforceApplicationType::Worker, 'DEMO Kabul Field Worker', 'demo.worker.kabul@civictrack.test'],
        ] as [$role, $membershipType, $name, $email]) {
            DB::transaction(function () use ($role, $membershipType, $name, $email, $password, $kabulUnit): void {
                $user = User::query()->firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => $password,
                        'email_verified_at' => now(),
                        'account_status' => AccountStatus::Active,
                    ],
                );

                if (! $user->wasRecentlyCreated) {
                    return;
                }

                $user->assignRole($role->value);

                if ($membershipType === null) {
                    $user->citizenProfile()->create([
                        'preferred_locale' => 'en',
                        'privacy_notice_version' => config('civictrack.privacy_notice_version'),
                        'privacy_acknowledged_at' => now(),
                    ]);

                    return;
                }

                $profile = $membershipType === WorkforceApplicationType::Staff
                    ? $user->staffProfile()
                    : $user->workerProfile();
                $profile->create([
                    'approved_at' => now(),
                    'position_title' => 'DEMO '.$membershipType->value,
                ]);
                $user->unitMemberships()->create([
                    'organization_unit_id' => $kabulUnit->id,
                    'membership_type' => $membershipType,
                    'status' => WorkforceMembershipStatus::Active,
                    'position_title' => 'DEMO '.$membershipType->value,
                    'started_at' => now(),
                ]);
            });
        }
    }
}
