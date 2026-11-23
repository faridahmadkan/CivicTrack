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
        Schema::create('worker_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_assignment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('quality_rating');
            $table->unsignedTinyInteger('safety_rating');
            $table->unsignedTinyInteger('professionalism_rating');
            $table->text('comment')->nullable();
            $table->timestamp('rated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_ratings');
    }
};
