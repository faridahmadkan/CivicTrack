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
        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->foreignId('approved_application_id')->nullable()->change();
        });

        Schema::table('worker_profiles', function (Blueprint $table): void {
            $table->foreignId('approved_application_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('staff_profiles')->whereNull('approved_application_id')->exists()
            || DB::table('worker_profiles')->whereNull('approved_application_id')->exists()) {
            throw new LogicException('Admin-provisioned workforce profiles exist; this migration cannot make their application links required.');
        }

        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->foreignId('approved_application_id')->nullable(false)->change();
        });

        Schema::table('worker_profiles', function (Blueprint $table): void {
            $table->foreignId('approved_application_id')->nullable(false)->change();
        });
    }
};
