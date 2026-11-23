<?php

namespace App\Models;

use Database\Factories\WorkforceApplicationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class WorkforceApplicationEvent extends Model
{
    /** @use HasFactory<WorkforceApplicationEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['workforce_application_id', 'actor_id', 'event_type', 'from_status', 'to_status', 'reason', 'properties', 'occurred_at'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(WorkforceApplication::class, 'workforce_application_id');
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
            throw new LogicException('Workforce application history is append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Workforce application history is append-only.');
        });
    }
}
