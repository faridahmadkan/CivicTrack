<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportRelationship extends Model
{
    protected $fillable = [
        'report_id', 'related_report_id', 'relationship_type', 'reason', 'created_by', 'properties',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'report_id');
    }

    public function relatedReport(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'related_report_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }
}
