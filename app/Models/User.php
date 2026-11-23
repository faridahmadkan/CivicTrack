<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'account_status', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function citizenProfile(): HasOne
    {
        return $this->hasOne(CitizenProfile::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function workerProfile(): HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }

    public function workforceApplications(): HasMany
    {
        return $this->hasMany(WorkforceApplication::class);
    }

    public function civicReports(): HasMany
    {
        return $this->hasMany(CivicReport::class);
    }

    public function assignedCivicReports(): HasMany
    {
        return $this->hasMany(CivicReport::class, 'assigned_staff_id');
    }

    public function reportComments(): HasMany
    {
        return $this->hasMany(ReportComment::class, 'actor_id');
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class, 'worker_id');
    }

    public function serviceFeedback(): HasMany
    {
        return $this->hasMany(ServiceFeedback::class);
    }

    public function reportReopenRequests(): HasMany
    {
        return $this->hasMany(ReportReopenRequest::class, 'requester_id');
    }

    public function workerRatingsGiven(): HasMany
    {
        return $this->hasMany(WorkerRating::class, 'rated_by');
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class, 'actor_id');
    }

    public function unitMemberships(): HasMany
    {
        return $this->hasMany(UnitMembership::class);
    }

    public function isActive(): bool
    {
        return $this->account_status === AccountStatus::Active;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_status' => AccountStatus::class,
            'status_changed_at' => 'datetime',
        ];
    }
}
