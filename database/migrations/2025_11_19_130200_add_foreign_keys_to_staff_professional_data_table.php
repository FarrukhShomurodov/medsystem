<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * staff_professional_data.speciality_id and department_id had no foreign keys, so deleting a
 * specialty or department in use left staff pointing at nothing and broke their record pages.
 * The keys are added NOT VALID: they protect every new row and every delete from now on without
 * failing on orphans an existing database may already contain.
 */
return new class extends Migration
{
    private const array KEYS = [
        'staff_professional_data_speciality_id_foreign' => ['speciality_id', 'specialties'],
        'staff_professional_data_department_id_foreign' => ['department_id', 'departments'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::KEYS as $name => [$column, $table]) {
            DB::statement("ALTER TABLE staff_professional_data ADD CONSTRAINT {$name} FOREIGN KEY ({$column}) REFERENCES {$table} (id) ON DELETE RESTRICT NOT VALID");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (array_keys(self::KEYS) as $name) {
            DB::statement("ALTER TABLE staff_professional_data DROP CONSTRAINT IF EXISTS {$name}");
        }
    }
};
