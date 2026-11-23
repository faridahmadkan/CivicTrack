<?php

namespace App\Models;

use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use Database\Factories\UnitMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitMembership extends Model
{
    /** @use HasFactory<UnitMembershipFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'organization_unit_id', 'created_by', 'membership_type', 'status', 'position_title', 'started_at', 'ended_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'membership_type' => WorkforceApplicationType::class,
            'status' => WorkforceMembershipStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}
