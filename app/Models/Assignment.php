<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['report_id', 'staff_id', 'assigned_by', 'assigned_at', 'notes', 'status'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
