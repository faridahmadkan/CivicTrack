<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAccountProvisioningService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    /**
     * @param  array{name: string, email: string, role: string, organization_unit_id?: int|string|null, position_title?: string|null}  $attributes
     */
    public function create(User $administrator, array $attributes): User
    {
        $role = RoleName::from($attributes['role']);
        $membershipType = match ($role) {
            RoleName::Staff => WorkforceApplicationType::Staff,
            RoleName::Worker => WorkforceApplicationType::Worker,
            default => null,
        };

        try {
            return DB::transaction(function () use ($administrator, $attributes, $role, $membershipType): User {
                $unit = $membershipType === null
                    ? null
                    : OrganizationUnit::query()
                        ->whereKey($attributes['organization_unit_id'])
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                if ($membershipType !== null && $unit === null) {
                    throw ValidationException::withMessages([
                        'organization_unit_id' => 'Choose an active organization unit.',
                    ]);
                }

                $user = User::query()->create([
                    'name' => trim($attributes['name']),
                    'email' => mb_strtolower(trim($attributes['email'])),
                    'password' => Str::random(64),
                    'account_status' => AccountStatus::Active,
                    'email_verified_at' => config('civictrack.require_email_verification') ? null : now(),
                ]);
                $user->assignRole($role->value);

                if ($membershipType !== null && $unit !== null) {
                    $positionTitle = trim((string) $attributes['position_title']);
                    $profile = $membershipType === WorkforceApplicationType::Staff
                        ? $user->staffProfile()
                        : $user->workerProfile();
                    $profile->create([
                        'approved_by' => $administrator->id,
                        'approved_at' => now(),
                        'position_title' => $positionTitle,
                    ]);
                    $user->unitMemberships()->create([
                        'organization_unit_id' => $unit->id,
                        'created_by' => $administrator->id,
                        'membership_type' => $membershipType,
                        'status' => WorkforceMembershipStatus::Active,
                        'position_title' => $positionTitle,
                        'started_at' => now(),
                    ]);
                }

                $this->auditWriter->record(
                    'account.admin_provisioned',
                    $user,
                    [
                        'role' => $role->value,
                        'organization_unit_id' => $unit?->id,
                        'email_verification_required' => config('civictrack.require_email_verification'),
                    ],
                    $administrator,
                );

                return $user;
            });
        } catch (QueryException $exception) {
            if (User::query()->where('email', mb_strtolower(trim($attributes['email'])))->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'An account with this email address has already been created.',
                ]);
            }

            throw $exception;
        }
    }
}
