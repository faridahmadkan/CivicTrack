<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number', 'citizen_id', 'category_id', 'department_id', 'title',
        'description', 'location', 'latitude', 'longitude', 'priority', 'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attachments()
    {
        return $this->hasMany(ReportAttachment::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(ReportStatusHistory::class);
    }

    public function progressUpdates()
    {
        return $this->hasMany(ProgressUpdate::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }
}
