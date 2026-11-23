<?php

namespace App\Models;

use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use Database\Factories\WorkforceApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkforceApplication extends Model
{
    /** @use HasFactory<WorkforceApplicationFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'requested_unit_id', 'reviewed_by', 'application_number', 'application_type', 'status', 'position_title', 'experience_summary', 'qualification_summary', 'additional_information', 'submitted_at', 'reviewed_at', 'requested_information', 'decision_reason'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class, 'requested_unit_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkforceApplicationEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(WorkforceApplicationDocument::class)->orderBy('id');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            WorkforceApplicationStatus::Submitted,
            WorkforceApplicationStatus::UnderReview,
            WorkforceApplicationStatus::InformationRequested,
        ], true);
    }

    protected function casts(): array
    {
        return [
            'application_type' => WorkforceApplicationType::class,
            'status' => WorkforceApplicationStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }
}
