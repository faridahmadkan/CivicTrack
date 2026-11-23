<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['actor_id', 'event_key', 'subject_type', 'subject_id', 'properties', 'ip_address', 'user_agent', 'occurred_at'])]
class AuditEvent extends Model
{
    /** @use HasFactory<\Database\Factories\AuditEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Audit events are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Audit events are append-only.');
        });
    }
}
