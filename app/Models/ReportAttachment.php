<?php

namespace App\Models;

use Database\Factories\ReportAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Hidden(['storage_path', 'sha256'])]
class ReportAttachment extends Model
{
    /** @use HasFactory<ReportAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'civic_report_id', 'uploaded_by', 'storage_disk', 'storage_path', 'original_name', 'mime_type', 'size_bytes', 'sha256', 'is_public',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }
}
