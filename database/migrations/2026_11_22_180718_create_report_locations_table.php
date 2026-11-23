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
        Schema::create('report_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('civic_report_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('public_latitude', 10, 4)->nullable();
            $table->decimal('public_longitude', 10, 4)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('privacy_level', 24)->default('precise_private');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_locations');
    }
};
