<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * A staff position used to find its spatie role by name, so renaming the position left its staff
 * on the old role and a position named like a system role took that role over.
 * Positions now point at their role by id; existing positions are linked to the role of the same
 * name on the "admin" guard, or to a new role when that name is a system role or already taken.
 */
return new class extends Migration
{
    private const string GUARD = 'admin';

    private const array SYSTEM_ROLES = ['main_admin'];

    public function up(): void
    {
        $roles = config('permission.table_names.roles');

        Schema::table('staff_positions', function (Blueprint $table) use ($roles): void {
            $table->foreignId('role_id')->nullable()->unique()->constrained($roles)->restrictOnDelete();
        });

        foreach (DB::table('staff_positions')->orderBy('id')->get(['id', 'name']) as $position) {
            DB::table('staff_positions')
                ->where('id', $position->id)
                ->update(['role_id' => $this->roleIdFor($roles, $position->id, $position->name)]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('staff_positions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
        });
    }

    private function roleIdFor(string $roles, int $positionId, string $name): int
    {
        $existing = in_array($name, self::SYSTEM_ROLES, true) ? null : DB::table($roles)
            ->where('guard_name', self::GUARD)
            ->where('name', $name)
            ->whereNotIn('id', DB::table('staff_positions')->whereNotNull('role_id')->select('role_id'))
            ->value('id');

        if ($existing !== null) {
            return $existing;
        }

        $taken = DB::table($roles)->where('guard_name', self::GUARD)->where('name', $name)->exists();

        return DB::table($roles)->insertGetId([
            'name' => $taken ? "{$name} #{$positionId}" : $name,
            'guard_name' => self::GUARD,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
