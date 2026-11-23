<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);
        }

        $permissionsByRole = [
            RoleName::Administrator->value => array_map(
                static fn (PermissionName $permission): string => $permission->value,
                PermissionName::cases(),
            ),
            RoleName::Staff->value => [
                PermissionName::ReportsViewDepartment->value,
                PermissionName::ReportsTriageDepartment->value,
                PermissionName::WorkOrdersManageDepartment->value,
                PermissionName::AssignmentsManageDepartment->value,
                PermissionName::VerificationManageDepartment->value,
                PermissionName::FeedbackRateWorkers->value,
                PermissionName::AnalyticsViewDepartment->value,
            ],
            RoleName::Worker->value => [
                PermissionName::AssignmentsViewOwn->value,
                PermissionName::AssignmentsUpdateOwn->value,
                PermissionName::AssignmentsEvidenceCreateOwn->value,
            ],
            RoleName::Citizen->value => [
                PermissionName::ReportsCreate->value,
                PermissionName::ReportsViewOwn->value,
                PermissionName::ReportsCommentOwn->value,
                PermissionName::FeedbackCreateOwn->value,
            ],
        ];

        foreach ($permissionsByRole as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
