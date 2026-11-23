<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\RegistrationControl;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationControlService
{
    public function isOpen(RoleName $context): bool
    {
        return RegistrationControl::query()
            ->where('context', $context->value)
            ->first()
            ?->isOpenAt() ?? false;
    }

    public function lockOpenContext(RoleName $context): void
    {
        $registrationControl = RegistrationControl::query()
            ->where('context', $context->value)
            ->lockForUpdate()
            ->first();

        if (! $registrationControl?->isOpenAt()) {
            throw ValidationException::withMessages([
                'registration' => 'Citizen registration is currently unavailable. Please check back later.',
            ]);
        }
    }

    public function configure(
        RoleName $context,
        bool $enabled,
        ?\Illuminate\Support\Carbon $opensAt,
        ?\Illuminate\Support\Carbon $closesAt,
        User $actor,
        AuditWriter $auditWriter,
    ): RegistrationControl {
        if ($context === RoleName::Administrator) {
            throw ValidationException::withMessages(['context' => 'Administrator access cannot be publicly registered.']);
        }

        if ($opensAt !== null && $closesAt !== null && $closesAt->lessThanOrEqualTo($opensAt)) {
            throw ValidationException::withMessages(['closes_at' => 'The closing time must be after the opening time.']);
        }

        return DB::transaction(function () use ($context, $enabled, $opensAt, $closesAt, $actor, $auditWriter): RegistrationControl {
            $control = RegistrationControl::query()
                ->where('context', $context->value)
                ->lockForUpdate()
                ->first();

            if ($control === null) {
                throw ValidationException::withMessages(['context' => 'Registration settings have not been initialized.']);
            }

            $control->update([
                'enabled' => $enabled,
                'opens_at' => $opensAt,
                'closes_at' => $closesAt,
                'changed_by' => $actor->id,
            ]);

            $auditWriter->record(
                'registration_control.updated',
                $control,
                ['context' => $context->value, 'enabled' => $enabled],
                $actor,
            );

            return $control;
        });
    }
}
