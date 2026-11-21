<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // progress_updates: staff_id should restrict on delete (preserve progress history)
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->foreign('staff_id')->references('id')->on('users')->restrictOnDelete();
        });

        // report_attachments: uploaded_by should restrict on delete
        Schema::table('report_attachments', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->foreign('uploaded_by')->references('id')->on('users')->restrictOnDelete();
        });

        // progress_attachments: no direct user FK, but progress_update_id cascade is OK

        // assignments: staff_id should restrict on delete (preserve assignment history)
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->foreign('staff_id')->references('id')->on('users')->restrictOnDelete();
        });

        // report_status_histories: updated_by should restrict on delete
        Schema::table('report_status_histories', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->foreign('updated_by')->references('id')->on('users')->restrictOnDelete();
        });

        // feedback: citizen_id should restrict on delete
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropForeign(['citizen_id']);
            $table->foreign('citizen_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->foreign('staff_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('report_attachments', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->foreign('staff_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('report_status_histories', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->foreign('updated_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->dropForeign(['citizen_id']);
            $table->foreign('citizen_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};