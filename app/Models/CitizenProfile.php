<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read User $user
 */
class CitizenProfile extends Model
{
    /** @use HasFactory<\Database\Factories\CitizenProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'preferred_locale',
        'privacy_notice_version',
        'privacy_acknowledged_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'privacy_acknowledged_at' => 'datetime',
        ];
    }
}
