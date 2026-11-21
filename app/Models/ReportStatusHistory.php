<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportStatusHistory extends Model
{
    protected $fillable = ['report_id', 'updated_by', 'previous_status', 'new_status', 'notes'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
