<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Deletes the permissions an existing database still holds although PermissionCatalog no longer
 * lists them: nothing checks them, yet they stay on the staff position form and in the roles.
 */
#[Signature('permissions:prune {--force : Delete the permissions instead of only listing them}')]
#[Description('List the permissions PermissionCatalog does not hold; --force deletes them')]
class PrunePermissions extends Command
{
    public function handle(PermissionRegistrar $registrar): int
    {
        $stale = Permission::query()
            ->with('roles:id,name')
            ->where(fn (Builder $query) => $query
                ->where('guard_name', '!=', Admin::GUARD)
                ->orWhereNotIn('name', PermissionCatalog::names()))
            ->orderBy('guard_name')
            ->orderBy('name')
            ->get();

        if ($stale->isEmpty()) {
            $this->components->info('Every permission in the database is in PermissionCatalog.');

            return self::SUCCESS;
        }

        $this->table(
            ['Permission', 'Guard', 'Roles'],
            $stale->map(fn (Permission $permission): array => [
                $permission->name,
                $permission->guard_name,
                $permission->roles->pluck('name')->implode(', '),
            ]),
        );

        if (! $this->option('force')) {
            $this->components->warn("Dry run: {$stale->count()} permissions would be deleted. Run it with --force to delete them.");

            return self::SUCCESS;
        }

        // A query delete: the role and account pivots cascade in the database, whereas the model
        // events would resolve the user model of the permission's guard, which fails for a guard
        // the application no longer configures.
        DB::transaction(fn () => Permission::query()->whereKey($stale->modelKeys())->delete());
        $registrar->forgetCachedPermissions();

        $this->components->info("Deleted {$stale->count()} permissions.");

        return self::SUCCESS;
    }
}
