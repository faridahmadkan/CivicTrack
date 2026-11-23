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
        Schema::create('report_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('civic_reports')->cascadeOnDelete();
            $table->foreignId('related_report_id')->constrained('civic_reports')->cascadeOnDelete();
            $table->string('relationship_type', 24);
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->unique(['report_id', 'related_report_id', 'relationship_type'], 'report_relationships_pair_type_unique');
            $table->index(['related_report_id', 'relationship_type']);
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            IlluminateSupportFacadesDB::statement("CREATE TRIGGER report_relationships_no_self_insert BEFORE INSERT ON report_relationships WHEN NEW.report_id = NEW.related_report_id BEGIN SELECT RAISE(ABORT, 'A report cannot be related to itself'); END");
            IlluminateSupportFacadesDB::statement("CREATE TRIGGER report_relationships_no_self_update BEFORE UPDATE ON report_relationships WHEN NEW.report_id = NEW.related_report_id BEGIN SELECT RAISE(ABORT, 'A report cannot be related to itself'); END");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS report_relationships_no_self_insert');
            Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS report_relationships_no_self_update');
        }

        Schema::dropIfExists('report_relationships');
    }
};
