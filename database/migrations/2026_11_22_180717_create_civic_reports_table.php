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
        Schema::create('civic_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('routed_unit_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('public_reference', 32)->unique();
            $table->string('title', 160);
            $table->text('description');
            $table->string('status', 32)->default('submitted');
            $table->string('priority', 16)->default('normal');
            $table->boolean('is_public')->default(false);
            $table->json('category_answers')->nullable();
            $table->text('guest_contact_email')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority', 'submitted_at']);
            $table->index(['routed_unit_id', 'status', 'submitted_at']);
            $table->index(['user_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('civic_reports');
    }
};
