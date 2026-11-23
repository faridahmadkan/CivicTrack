<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_sla_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('civic_report_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('cycle_number')->default(1);
            $table->foreignId('report_sla_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_name_snapshot', 120)->nullable();
            $table->unsignedSmallInteger('policy_version_snapshot')->nullable();
            $table->unsignedInteger('first_response_target_minutes')->nullable();
            $table->unsignedInteger('resolution_target_minutes')->nullable();
            $table->unsignedInteger('escalation_repeat_minutes')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('first_response_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('first_response_breached_at')->nullable();
            $table->timestamp('resolution_breached_at')->nullable();
            $table->timestamp('next_escalation_at')->nullable();
            $table->unsignedSmallInteger('escalation_count')->default(0);
            $table->string('state', 16)->default('active');
            $table->timestamps();

            $table->unique(['civic_report_id', 'cycle_number']);
            $table->index(['state', 'next_escalation_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_sla_instances');
    }
};
