<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || ($user->isStaff() && $assignment->staff_id === $user->id);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isStaff() && $assignment->staff_id === $user->id;
    }
}
