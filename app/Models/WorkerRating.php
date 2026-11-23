<?php

namespace App\Models;

use Database\Factories\WorkerRatingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerRating extends Model
{
    /** @use HasFactory<WorkerRatingFactory> */
    use HasFactory;

    protected $fillable = [
        'task_assignment_id', 'rated_by', 'quality_rating', 'safety_rating',
        'professionalism_rating', 'comment', 'rated_at',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_by');
    }

    protected function casts(): array
    {
        return ['rated_at' => 'datetime'];
    }
}
