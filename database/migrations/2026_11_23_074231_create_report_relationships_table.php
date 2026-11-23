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

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['sqlite', 'pgsql'], true)) {
            DB::statement("CREATE UNIQUE INDEX report_relationships_single_canonical_target ON report_relationships(report_id) WHERE relationship_type IN ('duplicate', 'merged_into')");
        }

        if ($driver === 'sqlite') {
            DB::statement("CREATE TRIGGER report_relationships_validate_insert BEFORE INSERT ON report_relationships WHEN NEW.report_id = NEW.related_report_id OR NEW.relationship_type NOT IN ('related', 'duplicate', 'merged_into') OR (NEW.relationship_type = 'related' AND NEW.report_id > NEW.related_report_id) BEGIN SELECT RAISE(ABORT, 'Invalid report relationship'); END");
            DB::statement("CREATE TRIGGER report_relationships_validate_update BEFORE UPDATE ON report_relationships WHEN NEW.report_id = NEW.related_report_id OR NEW.relationship_type NOT IN ('related', 'duplicate', 'merged_into') OR (NEW.relationship_type = 'related' AND NEW.report_id > NEW.related_report_id) BEGIN SELECT RAISE(ABORT, 'Invalid report relationship'); END");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['sqlite', 'pgsql'], true)) {
            DB::statement('DROP INDEX IF EXISTS report_relationships_single_canonical_target');
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS report_relationships_validate_insert');
            DB::statement('DROP TRIGGER IF EXISTS report_relationships_validate_update');
        }

        Schema::dropIfExists('report_relationships');
    }
};
