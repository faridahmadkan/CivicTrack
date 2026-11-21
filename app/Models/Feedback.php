<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';
    protected $fillable = ['report_id', 'citizen_id', 'rating', 'comment'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }
}
