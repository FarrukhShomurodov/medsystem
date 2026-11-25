<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// surveys.diagnosis_id was added by editing the already-applied create_surveys_table migration,
// so databases migrated before that edit never received it. This brings them in line.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('surveys', 'diagnosis_id')) {
            return;
        }

        Schema::table('surveys', function (Blueprint $table): void {
            $table->foreignId('diagnosis_id')->nullable()->after('survey_type_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Nothing to undo: the column belongs to the surveys table definition
        // (2025_03_26_182648_create_surveys_table); this migration only backfills it.
    }
};
