<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressAttachment extends Model
{
    protected $fillable = ['progress_update_id', 'file_path', 'original_name'];

    public function progressUpdate()
    {
        return $this->belongsTo(ProgressUpdate::class);
    }
}
