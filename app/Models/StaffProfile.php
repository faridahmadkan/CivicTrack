<?php

namespace App\Models;

use Database\Factories\StaffProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    /** @use HasFactory<StaffProfileFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'approved_application_id', 'approved_by', 'position_title', 'approved_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedApplication(): BelongsTo
    {
        return $this->belongsTo(WorkforceApplication::class, 'approved_application_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }
}
