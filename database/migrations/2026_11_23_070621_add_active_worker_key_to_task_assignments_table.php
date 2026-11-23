<?php

use Illuminate\Database\Migrations\Migration;
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
        Schema::table('task_assignments', function (Blueprint $table): void {
            $table->foreignId('active_worker_id')->nullable()->after('worker_id')->constrained('users')->restrictOnDelete();
        });

        DB::table('task_assignments')
            ->whereNull('ended_at')
            ->update(['active_worker_id' => DB::raw('worker_id')]);

        Schema::table('task_assignments', function (Blueprint $table): void {
            $table->unique(['work_task_id', 'active_worker_id'], 'task_assignments_active_worker_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_assignments', function (Blueprint $table): void {
            $table->dropUnique('task_assignments_active_worker_unique');
            $table->dropConstrainedForeignId('active_worker_id');
        });
    }
};
