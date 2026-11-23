<?php

namespace App\Models;

use Database\Factories\WorkforceApplicationDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Hidden(['storage_path', 'sha256'])]
class WorkforceApplicationDocument extends Model
{
    /** @use HasFactory<WorkforceApplicationDocumentFactory> */
    use HasFactory;

    protected $fillable = ['workforce_application_id', 'uploaded_by', 'storage_disk', 'storage_path', 'original_name', 'mime_type', 'size_bytes', 'sha256', 'document_type'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(WorkforceApplication::class, 'workforce_application_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
