<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CitizenRegistrationService
{
    public function __construct(
        private readonly RegistrationControlService $registrationControls,
        private readonly AuditWriter $auditWriter,
    ) {}

    /**
     * @param array{name: string, email: string, password: string, phone?: string|null} $attributes
     */
    public function register(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $this->registrationControls->lockOpenContext(RoleName::Citizen);

            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => mb_strtolower($attributes['email']),
                'password' => $attributes['password'],
                'account_status' => AccountStatus::Active,
            ]);

            $user->assignRole(RoleName::Citizen->value);
            $user->citizenProfile()->create([
                'phone' => $attributes['phone'] ?? null,
                'preferred_locale' => app()->getLocale(),
                'privacy_notice_version' => config('civictrack.privacy_notice_version'),
                'privacy_acknowledged_at' => now(),
            ]);

            $this->auditWriter->record(
                'citizen.registered',
                $user,
                ['registration_context' => RoleName::Citizen->value],
                $user,
            );

            return $user;
        });
    }
}
