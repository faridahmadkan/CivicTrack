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
        Schema::create('workforce_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_unit_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('application_number', 48)->unique();
            $table->string('application_type', 16)->index();
            $table->string('status', 32)->default('submitted')->index();
            $table->string('position_title', 120);
            $table->text('experience_summary')->nullable();
            $table->text('qualification_summary')->nullable();
            $table->text('additional_information')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('requested_information')->nullable();
            $table->text('decision_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'application_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workforce_applications');
    }
};
