<?php

namespace App\Models;

use Database\Factories\RoutingDecisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class RoutingDecision extends Model
{
    /** @use HasFactory<RoutingDecisionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['civic_report_id', 'routing_rule_id', 'organization_unit_id', 'source', 'reason', 'decided_at'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(RoutingRule::class, 'routing_rule_id');
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Routing decisions are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Routing decisions are append-only.');
        });
    }
}
