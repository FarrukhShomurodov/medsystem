<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// patient_diagnoses.diagnosis_id always held a single diagnoses.id, but as untyped text without a
// constraint, so joins against the bigint key fail on PostgreSQL and orphans were possible.
return new class extends Migration
{
    public function up(): void
    {
        // Rows that do not point at an existing diagnosis cannot be rendered and cannot satisfy the key.
        DB::table('patient_diagnoses')
            ->whereNotIn('diagnosis_id', DB::table('diagnoses')->selectRaw('cast(id as text)'))
            ->delete();

        Schema::table('patient_diagnoses', function (Blueprint $table): void {
            $table->unsignedBigInteger('diagnosis_id')->using('diagnosis_id::bigint')->change();
            $table->foreign('diagnosis_id')->references('id')->on('diagnoses')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patient_diagnoses', function (Blueprint $table): void {
            $table->dropForeign(['diagnosis_id']);
            $table->text('diagnosis_id')->using('diagnosis_id::text')->change();
        });
    }
};
