<?php

namespace App\Models;

use App\Enums\ReportCommentVisibility;
use Database\Factories\ReportCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ReportComment extends Model
{
    /** @use HasFactory<ReportCommentFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['civic_report_id', 'actor_id', 'author_label', 'visibility', 'body', 'created_at'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CivicReport::class, 'civic_report_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return ['visibility' => ReportCommentVisibility::class, 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Report comments are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Report comments are append-only.');
        });
    }
}
