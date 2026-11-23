<?php

namespace App\Models;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use Database\Factories\ReportStatusEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ReportStatusEvent extends Model
{
    /** @use HasFactory<ReportStatusEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'civic_report_id', 'actor_id', 'event_type', 'from_status', 'to_status', 'from_priority', 'to_priority',
        'reason', 'citizen_visible', 'properties', 'occurred_at',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return [
            'from_status' => CivicReportStatus::class,
            'to_status' => CivicReportStatus::class,
            'from_priority' => CivicReportPriority::class,
            'to_priority' => CivicReportPriority::class,
            'citizen_visible' => 'boolean',
            'properties' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Report status history is append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Report status history is append-only.');
        });
    }
}
