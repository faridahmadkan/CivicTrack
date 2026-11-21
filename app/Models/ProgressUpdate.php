<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressUpdate extends Model
{
    use HasFactory;

    protected $fillable = ['report_id', 'staff_id', 'description', 'is_internal'];

    protected function casts(): array
    {
        return ['is_internal' => 'boolean'];
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function attachments()
    {
        return $this->hasMany(ProgressAttachment::class);
    }
}
