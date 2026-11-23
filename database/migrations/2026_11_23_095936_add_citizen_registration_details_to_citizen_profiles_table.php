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
        Schema::table('citizen_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('locality', 120)->nullable();
            $table->decimal('home_latitude', 10, 7)->nullable();
            $table->decimal('home_longitude', 10, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citizen_profiles', function (Blueprint $table) {
            $table->dropColumn(['age', 'locality', 'home_latitude', 'home_longitude']);
        });
    }
};
