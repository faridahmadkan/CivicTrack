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
        Schema::create('workforce_application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workforce_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('storage_disk', 32)->default('local');
            $table->string('storage_path', 512)->unique();
            $table->string('original_name', 180);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('document_type', 32)->default('supporting');
            $table->timestamps();
            $table->index(['workforce_application_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workforce_application_documents');
    }
};
