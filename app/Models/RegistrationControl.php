<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationControl extends Model
{
    /** @use HasFactory<\Database\Factories\RegistrationControlFactory> */
    use HasFactory;

    protected $fillable = [
        'context',
        'enabled',
        'opens_at',
        'closes_at',
        'changed_by',
    ];

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function isOpenAt(?\Illuminate\Support\Carbon $time = null): bool
    {
        $time ??= now();

        return $this->enabled
            && ($this->opens_at === null || $this->opens_at->lessThanOrEqualTo($time))
            && ($this->closes_at === null || $this->closes_at->greaterThan($time));
    }

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }
}
