<?php

namespace App\Models;

use Database\Factories\SlaEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class SlaEvent extends Model
{
    /** @use HasFactory<SlaEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'report_sla_instance_id', 'actor_id', 'event_type', 'target', 'details', 'properties', 'occurred_at',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(ReportSlaInstance::class, 'report_sla_instance_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return ['properties' => 'array', 'occurred_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('SLA events are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('SLA events are append-only.');
        });
    }
}
