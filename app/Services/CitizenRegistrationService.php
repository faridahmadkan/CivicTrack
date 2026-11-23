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
     * @param  array{name: string, email: string, password: string, phone?: string|null, age: int|string, locality: string, home_latitude?: numeric-string|float|null, home_longitude?: numeric-string|float|null}  $attributes
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
                'email_verified_at' => config('civictrack.require_email_verification') ? null : now(),
            ]);

            $user->assignRole(RoleName::Citizen->value);
            $user->citizenProfile()->create([
                'phone' => $attributes['phone'] ?? null,
                'age' => $attributes['age'],
                'locality' => trim($attributes['locality']),
                'home_latitude' => isset($attributes['home_latitude']) ? round((float) $attributes['home_latitude'], 3) : null,
                'home_longitude' => isset($attributes['home_longitude']) ? round((float) $attributes['home_longitude'], 3) : null,
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
