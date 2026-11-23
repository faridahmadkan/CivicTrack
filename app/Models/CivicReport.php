<?php

namespace App\Models;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use Database\Factories\CivicReportFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Hidden(['guest_contact_email'])]
class CivicReport extends Model
{
    /** @use HasFactory<CivicReportFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'service_category_id', 'routed_unit_id', 'assigned_staff_id', 'reviewed_by', 'public_reference',
        'title', 'description', 'status', 'priority', 'is_public', 'category_answers', 'guest_contact_email',
        'submitted_at', 'acknowledged_at', 'resolved_at', 'closed_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'public_reference';
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function routedUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class, 'routed_unit_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function location(): HasOne
    {
        return $this->hasOne(ReportLocation::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(ReportStatusEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReportComment::class)->orderBy('created_at')->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class)->orderBy('id');
    }

    public function guestAccessTokens(): HasMany
    {
        return $this->hasMany(GuestAccessToken::class);
    }

    public function routingDecisions(): HasMany
    {
        return $this->hasMany(RoutingDecision::class)->orderBy('decided_at')->orderBy('id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class)->orderBy('created_at')->orderBy('id');
    }

    public function slaInstance(): HasOne
    {
        return $this->hasOne(ReportSlaInstance::class)->latestOfMany('cycle_number');
    }

    public function slaInstances(): HasMany
    {
        return $this->hasMany(ReportSlaInstance::class)->orderBy('cycle_number');
    }

    public function serviceFeedback(): HasOne
    {
        return $this->hasOne(ServiceFeedback::class);
    }

    public function reopenRequests(): HasMany
    {
        return $this->hasMany(ReportReopenRequest::class)->orderByDesc('requested_at');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    protected function casts(): array
    {
        return [
            'status' => CivicReportStatus::class,
            'priority' => CivicReportPriority::class,
            'is_public' => 'boolean',
            'category_answers' => 'array',
            'guest_contact_email' => 'encrypted',
            'submitted_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
            'reopened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
