<?php

namespace App\Models;

use App\Enums\ReportReopenStatus;
use Database\Factories\ReportReopenRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportReopenRequest extends Model
{
    /** @use HasFactory<ReportReopenRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'civic_report_id', 'requester_id', 'reviewed_by', 'request_number', 'reason', 'status',
        'decision_reason', 'requested_at', 'reviewed_at',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected function casts(): array
    {
        return [
            'status' => ReportReopenStatus::class,
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }
}
