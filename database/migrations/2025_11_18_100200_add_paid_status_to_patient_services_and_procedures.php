<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The cashier marks paid services and procedures with status "paid", which the original
// enum CHECK constraints reject. On PostgreSQL Laravel enums are varchar + CHECK, so the
// constraint is replaced in place.
return new class extends Migration
{
    private const array TABLES = ['patient_services', 'patient_procedures'];

    private const array STATUSES = ['assigned', 'in_process', 'completed', 'cancelled', 'confirmed'];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABLES as $table) {
            $this->replaceStatusCheck($table, [...self::STATUSES, 'paid']);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABLES as $table) {
            // "paid" rows would violate the old constraint; they go back to the pre-payment status.
            DB::table($table)->where('status', 'paid')->update(['status' => 'confirmed']);

            $this->replaceStatusCheck($table, self::STATUSES);
        }
    }

    /**
     * @param  list<string>  $statuses
     */
    private function replaceStatusCheck(string $table, array $statuses): void
    {
        $constraint = "{$table}_status_check";
        $allowed = implode(', ', array_map(fn (string $status) => DB::getPdo()->quote($status), $statuses));

        DB::statement("alter table {$table} drop constraint if exists {$constraint}");
        DB::statement("alter table {$table} add constraint {$constraint} check (status in ({$allowed}))");
    }
};
