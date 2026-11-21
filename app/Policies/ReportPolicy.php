<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(User $user, Report $report): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isCitizen()) {
            return $report->citizen_id === $user->id;
        }

        return $report->assignments()->where('staff_id', $user->id)->exists();
    }

    public function update(User $user, Report $report): bool
    {
        return $user->isAdmin()
            || ($user->isCitizen() && $report->citizen_id === $user->id
                && in_array($report->status, ['pending_review', 'rejected'], true));
    }

    public function cancel(User $user, Report $report): bool
    {
        return $user->isCitizen()
            && $report->citizen_id === $user->id
            && in_array($report->status, ['pending_review', 'approved'], true);
    }
}
