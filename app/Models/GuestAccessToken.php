<?php

namespace App\Models;

use Database\Factories\GuestAccessTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Hidden(['token_hash'])]
class GuestAccessToken extends Model
{
    /** @use HasFactory<GuestAccessTokenFactory> */
    use HasFactory;

    protected $fillable = ['civic_report_id', 'token_hash', 'expires_at', 'revoked_at', 'last_used_at'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function isUsable(): bool
    {
        return $this->revoked_at === null && $this->expires_at->isFuture();
    }

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'revoked_at' => 'datetime', 'last_used_at' => 'datetime'];
    }
}
