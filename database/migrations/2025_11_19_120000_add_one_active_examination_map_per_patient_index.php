<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A patient has at most one open hospital stay: the pages pick "the" active examination map of a
 * patient, and a double-submitted admission form used to open a second one.
 */
return new class extends Migration
{
    private const string INDEX = 'examination_maps_one_active_per_patient';

    public function up(): void
    {
        // Close the older duplicates a double submit left behind, keeping the latest admission
        // open; without this the unique index cannot be built on an existing database.
        DB::statement(<<<'SQL'
            UPDATE examination_maps
            SET is_active = false, updated_at = now()
            WHERE is_active
              AND id NOT IN (
                  SELECT DISTINCT ON (patient_id) id
                  FROM examination_maps
                  WHERE is_active
                  ORDER BY patient_id, admission_date DESC, id DESC
              )
            SQL);

        DB::statement('CREATE UNIQUE INDEX '.self::INDEX.' ON examination_maps (patient_id) WHERE is_active');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS '.self::INDEX);
    }
};
