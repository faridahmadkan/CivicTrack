<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditWriter
{
    public function __construct(private readonly Request $request) {}

    /**
     * @param array<string, bool|float|int|string|null> $properties
     */
    public function record(string $eventKey, ?Model $subject = null, array $properties = [], ?User $actor = null): AuditEvent
    {
        return AuditEvent::query()->create([
            'actor_id' => $actor?->getKey(),
            'event_key' => $eventKey,
            'subject_type' => $subject === null ? null : $subject::class,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent() === null
                ? null
                : Str::limit($this->request->userAgent(), 500, ''),
            'occurred_at' => now(),
        ]);
    }
}
