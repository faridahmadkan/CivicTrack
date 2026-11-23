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
        Schema::create('routing_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('civic_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routing_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('organization_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 32);
            $table->text('reason');
            $table->timestamp('decided_at');
            $table->timestamps();

            $table->index(['civic_report_id', 'decided_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_decisions');
    }
};
