<?php

use App\Models\Admin;
use App\Support\Authorization\PermissionCatalog;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

/*
| permissions:prune removes, from a database seeded by an older PermissionSeeder, the permissions
| the seeder no longer creates (the Analyze module's, the view_* ones no route checked ...).
*/

beforeEach(function (): void {
    seed(PermissionSeeder::class);

    // Left behind by earlier releases: a permission of the deleted laboratory module, held by a
    // role and directly by an account, and one created on the former "web" guard.
    $this->stale = Permission::findOrCreate('list_analyzes', Admin::GUARD);
    $this->otherGuard = Permission::findOrCreate('list_patients', 'web');
    $this->mainAdmin = Role::findByName(Admin::MAIN_ADMIN_ROLE, Admin::GUARD)->givePermissionTo($this->stale);
    $this->account = Admin::factory()->create()->givePermissionTo($this->stale);
});

it('only lists the permissions the seeder does not seed unless --force is given', function (): void {
    artisan('permissions:prune')
        ->expectsTable(['Permission', 'Guard', 'Roles'], [
            ['list_analyzes', 'admin', Admin::MAIN_ADMIN_ROLE],
            ['list_patients', 'web', ''],
        ])
        ->expectsOutputToContain('Dry run: 2 permissions would be deleted.')
        ->assertSuccessful();

    expect(Permission::query()->count())->toBe(count(PermissionCatalog::names()) + 2)
        ->and($this->account->fresh()->can('list_analyzes'))->toBeTrue();
});

it('deletes them with --force, together with what roles and accounts held of them', function (): void {
    artisan('permissions:prune', ['--force' => true])
        ->expectsOutputToContain('Deleted 2 permissions.')
        ->assertSuccessful();

    expect(Permission::query()->whereKey([$this->stale->id, $this->otherGuard->id])->exists())->toBeFalse()
        ->and(Permission::query()->pluck('name')->sort()->values()->all())->toBe(collect(PermissionCatalog::names())->sort()->values()->all())
        ->and($this->mainAdmin->fresh()->permissions()->count())->toBe(count(PermissionCatalog::names()))
        ->and($this->account->fresh()->getDirectPermissions())->toBeEmpty()
        ->and($this->account->fresh()->can('list_analyzes'))->toBeFalse();
});

it('has nothing to do on a database holding only the seeded permissions', function (): void {
    artisan('permissions:prune', ['--force' => true])->assertSuccessful();

    artisan('permissions:prune')
        ->expectsOutputToContain('Every permission in the database is in PermissionCatalog.')
        ->assertSuccessful();

    expect(Permission::query()->count())->toBe(count(PermissionCatalog::names()));
});
