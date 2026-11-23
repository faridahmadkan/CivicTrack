<?php

namespace App\Models;

use Database\Factories\ReportLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportLocation extends Model
{
    /** @use HasFactory<ReportLocationFactory> */
    use HasFactory;

    protected $fillable = [
        'civic_report_id', 'latitude', 'longitude', 'public_latitude', 'public_longitude', 'address', 'privacy_level',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }
}
