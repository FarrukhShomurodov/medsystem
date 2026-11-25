<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * An admin account holding exactly the given permissions.
 *
 * The permissions are created on the fly for the "admin" guard, so a test
 * that needs only a few of them does not have to run the full DatabaseSeeder.
 */
function adminWithPermissions(string ...$permissions): Admin
{
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, Admin::GUARD);
    }

    return tap(Admin::factory()->create())->givePermissionTo($permissions);
}
