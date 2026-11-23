<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_attachments', function (Blueprint $table) {
            $table->string('visibility', 16)->default('internal')->index();
        });

        DB::table('report_attachments')
            ->whereExists(static function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('civic_reports')
                    ->whereColumn('civic_reports.id', 'report_attachments.civic_report_id')
                    ->where(static function (Builder $reporterUploads): void {
                        $reporterUploads
                            ->whereColumn('report_attachments.uploaded_by', 'civic_reports.user_id')
                            ->orWhere(static function (Builder $guestUploads): void {
                                $guestUploads->whereNull('report_attachments.uploaded_by')
                                    ->whereNull('civic_reports.user_id');
                            });
                    });
            })
            ->update(['visibility' => 'reporter']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_attachments', function (Blueprint $table) {
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });
    }
};
