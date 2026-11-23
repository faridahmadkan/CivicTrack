<?php

namespace App\Models;

use Database\Factories\TaskEvidenceFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Hidden(['storage_path', 'sha256'])]
class TaskEvidence extends Model
{
    /** @use HasFactory<TaskEvidenceFactory> */
    use HasFactory;

    protected $fillable = [
        'work_task_id', 'task_assignment_id', 'uploaded_by', 'storage_disk', 'storage_path', 'original_name',
        'mime_type', 'size_bytes', 'sha256', 'evidence_type', 'caption',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
