<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A recommended action is marked done once per filled-in document. The nurse window marks it with
 * firstOrCreate, which falls back to the existing row on this key, so a double click sent as two
 * simultaneous requests cannot record the action twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Keep the first mark of each action; the later copies record the same thing again.
        DB::statement(<<<'SQL'
            DELETE FROM patient_document_score_finisheds
            WHERE id NOT IN (
                SELECT DISTINCT ON (document_response_id, document_score_id, document_action_id) id
                FROM patient_document_score_finisheds
                ORDER BY document_response_id, document_score_id, document_action_id, id
            )
            SQL);

        Schema::table('patient_document_score_finisheds', function (Blueprint $table): void {
            $table->unique(['document_response_id', 'document_score_id', 'document_action_id']);
        });
    }

    public function down(): void
    {
        Schema::table('patient_document_score_finisheds', function (Blueprint $table): void {
            $table->dropUnique(['document_response_id', 'document_score_id', 'document_action_id']);
        });
    }
};
