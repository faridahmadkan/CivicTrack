<?php

namespace App\Console\Commands;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\User;
use App\Services\AuditWriter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

#[Signature('civictrack:bootstrap-admin')]
#[Description('Create the first administrator from protected environment configuration.')]
class BootstrapAdministrator extends Command
{
    public function handle(AuditWriter $auditWriter): int
    {
        $name = trim((string) config('civictrack.bootstrap_admin.name'));
        $email = mb_strtolower(trim((string) config('civictrack.bootstrap_admin.email')));
        $password = (string) config('civictrack.bootstrap_admin.password');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 16) {
            $this->error('Bootstrap configuration is incomplete or does not meet the minimum security requirements.');

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error('An account with the configured administrator identity already exists; no changes were made.');

            return self::FAILURE;
        }

        if (! Role::query()->where('name', RoleName::Administrator->value)->where('guard_name', 'web')->exists()) {
            $this->error('Application roles are not initialized. Run the database seeders before bootstrapping an administrator.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($auditWriter, $email, $name, $password): void {
            $administrator = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
                'account_status' => AccountStatus::Active,
            ]);

            $administrator->assignRole(RoleName::Administrator->value);
            $auditWriter->record(
                'administrator.bootstrap_created',
                $administrator,
                ['source' => 'environment_bootstrap'],
                $administrator,
            );
        });

        $this->info('The initial administrator account was created. Remove the bootstrap password from the environment configuration.');

        return self::SUCCESS;
    }
}
