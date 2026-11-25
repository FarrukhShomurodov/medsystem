<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An account's "recently viewed patients" holds a patient once: the card records a view with an
 * upsert on this key, so two cards opened at the same moment cannot add the patient twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Keep the latest row of each account and patient; the older copies only pushed other
        // patients out of the list of ten.
        DB::statement(<<<'SQL'
            DELETE FROM last_viewed_patients
            WHERE id NOT IN (
                SELECT DISTINCT ON (admin_id, patient_id) id
                FROM last_viewed_patients
                ORDER BY admin_id, patient_id, updated_at DESC NULLS LAST, id DESC
            )
            SQL);

        Schema::table('last_viewed_patients', function (Blueprint $table): void {
            $table->unique(['admin_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::table('last_viewed_patients', function (Blueprint $table): void {
            $table->dropUnique(['admin_id', 'patient_id']);
        });
    }
};
