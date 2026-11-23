<?php

namespace App\Models;

use Database\Factories\ServiceFeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFeedback extends Model
{
    /** @use HasFactory<ServiceFeedbackFactory> */
    use HasFactory;

    protected $fillable = ['civic_report_id', 'user_id', 'rating', 'comment', 'submitted_at'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }
}
