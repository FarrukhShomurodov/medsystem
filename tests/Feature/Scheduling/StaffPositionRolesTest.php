<?php

use App\Models\Admin;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\StaffPositionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| Staff positions and the spatie roles their staff hold (ADR 0008).
*/

beforeEach(function (): void {
    $this->manager = adminWithPermissions('list_staff_positions', 'create_staff_positions', 'edit_staff_positions', 'delete_staff_positions');
    Permission::findOrCreate('list_patients', Admin::GUARD);
    Permission::findOrCreate('view_patients', Admin::GUARD);
});

it('creates a position together with its role and permissions on the admin guard', function (): void {
    actingAs($this->manager, 'admin')
        ->post(route('staffs.position.store'), ['name' => 'Лаборант', 'permissions' => ['list_patients']])
        ->assertRedirect(route('staffs.position.index'))
        ->assertSessionHas('success', 'Должность успешно добавлена!');

    $position = StaffPosition::query()->where('name', 'Лаборант')->sole();

    expect($position->role)
        ->name->toBe('Лаборант')
        ->guard_name->toBe(Admin::GUARD)
        ->and($position->role->permissions->pluck('name')->all())->toBe(['list_patients']);
});

it('renames the role with the position, so its staff keep the permissions edited afterwards', function (): void {
    // Regression: a rename created a new role while the staff kept the old, no longer edited one.
    $position = app(StaffPositionService::class)->store(['name' => 'Лаборант', 'permissions' => ['list_patients']]);
    $employee = Admin::factory()->create();
    $employee->assignRole($position->role);

    actingAs($this->manager, 'admin')
        ->put(route('staffs.position.update', $position), ['name' => 'Старший лаборант', 'permissions' => ['list_patients', 'view_patients']])
        ->assertRedirect(route('staffs.position.index'));

    expect(Role::query()->where('guard_name', Admin::GUARD)->pluck('name')->all())->not->toContain('Лаборант')
        ->and($position->fresh()->role->name)->toBe('Старший лаборант')
        ->and($employee->fresh()->hasPermissionTo('view_patients'))->toBeTrue();
});

it('never lets a position take over a system role', function (string $name): void {
    // Regression: a position named like a role rewrote that role's permissions.
    Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD)->givePermissionTo('list_patients', 'view_patients');
    Role::findOrCreate('Регистратор', Admin::GUARD);
    $mainAdminPermissions = Role::findByName(Admin::MAIN_ADMIN_ROLE, Admin::GUARD)->permissions()->count();

    actingAs($this->manager, 'admin')
        ->post(route('staffs.position.store'), ['name' => $name, 'permissions' => ['list_patients']])
        ->assertSessionHasErrors('name');

    expect(StaffPosition::query()->where('name', $name)->exists())->toBeFalse()
        ->and(Role::findByName(Admin::MAIN_ADMIN_ROLE, Admin::GUARD)->permissions()->count())->toBe($mainAdminPermissions);
})->with([Admin::MAIN_ADMIN_ROLE, 'Регистратор']);

it('refuses to rename a position to the name of another role', function (): void {
    Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD);
    seed(StaffPositionSeeder::class);
    $nurse = StaffPosition::query()->findOrFail(3);

    actingAs($this->manager, 'admin')
        ->put(route('staffs.position.update', $nurse), ['name' => Admin::MAIN_ADMIN_ROLE, 'permissions' => ['list_patients']])
        ->assertSessionHasErrors('name');

    actingAs($this->manager, 'admin')
        ->put(route('staffs.position.update', $nurse), ['name' => 'Кассир', 'permissions' => ['list_patients']])
        ->assertSessionHasErrors('name');

    expect($nurse->fresh()->role->name)->toBe('Медсестра');
});

it('labels every seeded permission on the position form in Russian', function (): void {
    // Permissions without a label showed up on the form under their code name.
    seed(PermissionSeeder::class);

    actingAs($this->manager, 'admin')
        ->get(route('staffs.position.create'))
        ->assertOk()
        ->assertViewHas('groupedPermissions', fn (array $groups): bool => collect($groups)
            ->flatten(1)
            ->filter(fn (array $permission): bool => $permission['russian_name'] === $permission['name'])
            ->isEmpty());
});

it('accepts only permissions of the admin guard', function (): void {
    Permission::findOrCreate('list_patients', 'web');

    actingAs($this->manager, 'admin')
        ->post(route('staffs.position.store'), ['name' => 'Лаборант', 'permissions' => ['list_patients', 'drop_database']])
        ->assertSessionHasErrors('permissions.1');

    expect(StaffPosition::query()->exists())->toBeFalse();
});

it('shows the permissions of the position role as checked on the edit form', function (): void {
    // The form looked the role up by the position name, so a position whose role name differs showed none.
    $position = app(StaffPositionService::class)->store(['name' => 'Лаборант', 'permissions' => ['view_patients']]);
    $position->role->update(['name' => 'Лаборатория']);

    actingAs($this->manager, 'admin')
        ->get(route('staffs.position.edit', $position))
        ->assertOk()
        ->assertViewHas('selectedPermissions', ['view_patients']);
});

it('links a position without a role to the role of its name on first use', function (): void {
    $role = Role::findOrCreate('Регистратор', Admin::GUARD);
    $position = StaffPosition::query()->create(['name' => 'Регистратор']);

    expect(app(StaffPositionService::class)->roleOf($position)->is($role))->toBeTrue()
        ->and($position->fresh()->role_id)->toBe($role->id);

    $squatter = StaffPosition::query()->create(['name' => Admin::MAIN_ADMIN_ROLE]);
    Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD);

    expect(app(StaffPositionService::class)->roleOf($squatter)->name)->toBe(Admin::MAIN_ADMIN_ROLE." #{$squatter->id}");
});

it('seeds the fixed positions linked to their roles, idempotently', function (): void {
    seed([PermissionSeeder::class, StaffPositionSeeder::class, StaffPositionSeeder::class]);

    expect(StaffPosition::query()->orderBy('id')->with('role')->get()->map(fn (StaffPosition $position): array => [$position->id, $position->role?->name])->all())
        ->toBe([[1, 'Доктор'], [2, 'Старшая медсестра'], [3, 'Медсестра'], [4, 'Заведующий'], [5, 'Регистратор'], [6, 'Кассир']])
        ->and(Role::findByName('Кассир', Admin::GUARD)->permissions)->not->toBeEmpty();
});

it('deletes a position together with its role but keeps the seeded positions', function (): void {
    seed(StaffPositionSeeder::class);
    $position = app(StaffPositionService::class)->store(['name' => 'Лаборант', 'permissions' => ['list_patients']]);
    $roleId = $position->role_id;

    actingAs($this->manager, 'admin')
        ->delete(route('staffs.position.destroy', $position))
        ->assertRedirect(route('staffs.position.index'));

    expect(Role::query()->whereKey($roleId)->exists())->toBeFalse();

    actingAs($this->manager, 'admin')
        ->from(route('staffs.position.index'))
        ->delete(route('staffs.position.destroy', StaffPosition::DOCTOR))
        ->assertSessionHas('error', 'Удаление этой должности недоступно!');

    expect(StaffPosition::query()->whereKey(StaffPosition::DOCTOR)->exists())->toBeTrue();
});

it('keeps a position held by staff, and its role', function (): void {
    $position = app(StaffPositionService::class)->store(['name' => 'Лаборант', 'permissions' => ['list_patients']]);
    Staff::factory()->withPosition('Лаборант')->create();

    actingAs($this->manager, 'admin')
        ->from(route('staffs.position.index'))
        ->delete(route('staffs.position.destroy', $position))
        ->assertSessionHas('error');

    expect($position->fresh()?->role)->not->toBeNull();
});

it('forbids managing positions without the position permissions', function (): void {
    $position = app(StaffPositionService::class)->store(['name' => 'Лаборант', 'permissions' => ['list_patients']]);
    $outsider = adminWithPermissions('list_staff_positions');

    actingAs($outsider, 'admin')
        ->post(route('staffs.position.store'), ['name' => 'Санитар', 'permissions' => ['list_patients']])
        ->assertForbidden();

    actingAs($outsider, 'admin')
        ->put(route('staffs.position.update', $position), ['name' => 'Санитар', 'permissions' => ['list_patients']])
        ->assertForbidden();

    expect($position->fresh()->name)->toBe('Лаборант');
});
