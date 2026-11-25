<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Database backstop against double booking: two appointments of the same staff member
 * that are not cancelled may not overlap in time. ReceptionService already refuses such a booking
 * under a row lock; this catches writes that bypass it.
 *
 * The staff id is compared as a one-value range so the GiST index needs no btree_gist extension.
 * Rows whose end is not after their start are invalid (new rows are refused by the CHECK); the
 * index treats them as empty so that legacy rows of that kind do not block the migration.
 *
 * An exclusion constraint cannot be added NOT VALID, and earlier versions, which did not check
 * overlaps, let real double bookings into existing databases. Those appointments took place or
 * need a registrar's decision, so the migration does not rewrite them: they are left out of the
 * constraint by id. ReceptionService still refuses a new booking that overlaps them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE receptions ADD CONSTRAINT receptions_end_after_start CHECK (end_time > start_time) NOT VALID');

        $doubleBooked = $this->doubleBookedIds();
        $exempt = $doubleBooked === [] ? '' : ' AND id NOT IN ('.implode(', ', $doubleBooked).')';

        DB::statement(<<<SQL
            ALTER TABLE receptions ADD CONSTRAINT receptions_no_overlap EXCLUDE USING gist (
                int8range(staff_id, staff_id, '[]') WITH &&,
                tsrange(date + start_time, date + greatest(start_time, end_time)) WITH &&
            ) WHERE (status <> 'cancelled'{$exempt})
            SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE receptions DROP CONSTRAINT IF EXISTS receptions_no_overlap');
        DB::statement('ALTER TABLE receptions DROP CONSTRAINT IF EXISTS receptions_end_after_start');
    }

    /**
     * The appointments that already overlap another one of the same staff member, by the rule
     * of the constraint (rows that end before they start count as empty and overlap nothing).
     *
     * @return list<int>
     */
    private function doubleBookedIds(): array
    {
        $rows = DB::select(<<<'SQL'
            SELECT DISTINCT a.id
            FROM receptions a
            JOIN receptions b
              ON b.staff_id = a.staff_id
             AND b.date = a.date
             AND b.id <> a.id
             AND b.start_time < a.end_time
             AND a.start_time < b.end_time
            WHERE a.status <> 'cancelled'
              AND b.status <> 'cancelled'
              AND a.start_time < a.end_time
              AND b.start_time < b.end_time
            ORDER BY a.id
            SQL);

        return array_map(intval(...), array_column($rows, 'id'));
    }
};
