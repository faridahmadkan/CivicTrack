<?php

namespace App\Models;

use App\Enums\ReportSlaState;
use Database\Factories\ReportSlaInstanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSlaInstance extends Model
{
    /** @use HasFactory<ReportSlaInstanceFactory> */
    use HasFactory;

    protected $fillable = [
        'civic_report_id', 'cycle_number', 'report_sla_policy_id', 'policy_name_snapshot', 'policy_version_snapshot',
        'first_response_target_minutes', 'resolution_target_minutes', 'escalation_repeat_minutes', 'started_at',
        'first_response_due_at', 'resolution_due_at', 'first_response_at', 'resolved_at',
        'first_response_breached_at', 'resolution_breached_at', 'next_escalation_at', 'escalation_count', 'state',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ReportSlaPolicy::class, 'report_sla_policy_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(SlaEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'state' => ReportSlaState::class,
            'started_at' => 'datetime',
            'first_response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'first_response_breached_at' => 'datetime',
            'resolution_breached_at' => 'datetime',
            'next_escalation_at' => 'datetime',
        ];
    }
}
