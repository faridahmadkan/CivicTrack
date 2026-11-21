<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportAttachment extends Model
{
    protected $fillable = ['report_id', 'uploaded_by', 'file_path', 'original_name', 'file_type'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
