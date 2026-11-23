<?php

namespace App\Models;

use Database\Factories\CitizenProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User $user
 */
#[Hidden(['phone', 'age', 'locality', 'home_latitude', 'home_longitude'])]
class CitizenProfile extends Model
{
    /** @use HasFactory<CitizenProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'age',
        'locality',
        'home_latitude',
        'home_longitude',
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
            'age' => 'integer',
            'home_latitude' => 'decimal:7',
            'home_longitude' => 'decimal:7',
        ];
    }
}
