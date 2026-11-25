<?php

use App\Exceptions\Scheduling\AccountException;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\StaffDocument;
use App\Services\AdminService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

/*
| Login accounts on the admins screen.
*/

beforeEach(function (): void {
    $this->manager = adminWithPermissions('list_admins', 'create_admins', 'edit_admins', 'delete_admins');
});

it('creates an account with a hashed password', function (): void {
    actingAs($this->manager, 'admin')
        ->post(route('admins.store'), ['email' => 'new@medsystem.test', 'password' => 'secret-password'])
        ->assertRedirect(route('admins.index'))
        ->assertSessionHas('success', 'Администратор успешно добавлен!');

    expect(Hash::check('secret-password', Admin::query()->where('email', 'new@medsystem.test')->sole()->password))->toBeTrue();
});

it('keeps the account\'s own email unique against the other accounts only', function (): void {
    // Admin\UpdateRequest had no unique rule: taking another account's email failed on the database.
    $admin = Admin::factory()->create(['email' => 'nurse@medsystem.test']);
    Admin::factory()->create(['email' => 'taken@medsystem.test']);

    actingAs($this->manager, 'admin')
        ->put(route('admins.update', $admin), ['email' => 'taken@medsystem.test'])
        ->assertSessionHasErrors('email');

    actingAs($this->manager, 'admin')
        ->put(route('admins.update', $admin), ['email' => 'nurse@medsystem.test', 'password' => ''])
        ->assertRedirect(route('admins.index'))
        ->assertSessionHasNoErrors();

    expect(Hash::check('password', $admin->fresh()->password))->toBeTrue();
});

it('changes the password only when a new one is given', function (): void {
    $admin = Admin::factory()->create();

    actingAs($this->manager, 'admin')
        ->put(route('admins.update', $admin), ['email' => $admin->email, 'password' => 'another-password'])
        ->assertRedirect(route('admins.index'));

    expect(Hash::check('another-password', $admin->fresh()->password))->toBeTrue();
});

it('refuses to delete the signed-in account', function (): void {
    actingAs($this->manager, 'admin')
        ->delete(route('admins.destroy', $this->manager))
        ->assertSessionHas('error', 'Нельзя удалить учётную запись, под которой вы вошли.');

    expect($this->manager->fresh())->not->toBeNull();
});

it('lets only a main administrator change or delete a main administrator\'s account', function (): void {
    // edit_admins and delete_admins were enough to take over or remove any main administrator.
    $mainAdmin = Admin::factory()->mainAdmin()->create(['email' => 'chief@medsystem.test']);

    actingAs($this->manager, 'admin')->get(route('admins.edit', $mainAdmin))->assertForbidden();
    actingAs($this->manager, 'admin')
        ->put(route('admins.update', $mainAdmin), ['email' => 'taken-over@medsystem.test', 'password' => 'attacker-password'])
        ->assertForbidden();
    actingAs($this->manager, 'admin')->delete(route('admins.destroy', $mainAdmin))->assertForbidden();

    expect($mainAdmin->fresh()->email)->toBe('chief@medsystem.test')
        ->and(Hash::check('password', $mainAdmin->fresh()->password))->toBeTrue();

    $this->manager->assignRole(Admin::MAIN_ADMIN_ROLE);

    actingAs($this->manager, 'admin')->get(route('admins.edit', $mainAdmin))->assertOk();
    actingAs($this->manager, 'admin')
        ->put(route('admins.update', $mainAdmin), ['email' => 'chief2@medsystem.test'])
        ->assertRedirect(route('admins.index'));
    actingAs($this->manager, 'admin')
        ->delete(route('admins.destroy', $mainAdmin))
        ->assertSessionHas('success', 'Администратор успешно удалён!');

    expect(Admin::query()->whereKey($mainAdmin->id)->exists())->toBeFalse();
});

it('lets a manager change the other accounts', function (): void {
    $nurse = Admin::factory()->create();

    actingAs($this->manager, 'admin')->get(route('admins.edit', $nurse))->assertOk();
    actingAs($this->manager, 'admin')
        ->delete(route('admins.destroy', $nurse))
        ->assertSessionHas('success', 'Администратор успешно удалён!');

    expect($nurse->fresh())->toBeNull();
});

it('never deletes the last main administrator who can still sign in', function (): void {
    // Only a main administrator may delete one, so on the admins screen another one is always
    // left; the service keeps the rule for any other caller. A dismissed one cannot sign in.
    $last = Admin::factory()->mainAdmin()->create();
    Admin::factory()->mainAdmin()->forStaff(Staff::factory()->has(StaffDocument::factory()->dismissed(), 'document')->create())->create();

    expect(fn () => app(AdminService::class)->destroy($last, $this->manager))
        ->toThrow(AccountException::class, 'Нельзя удалить последнего главного администратора.');

    $other = Admin::factory()->mainAdmin()->create();
    app(AdminService::class)->destroy($last, $other);

    expect($last->fresh())->toBeNull();
});

it('forbids changing accounts without the admin permissions', function (): void {
    $admin = Admin::factory()->create();
    $viewer = adminWithPermissions('list_admins');

    actingAs($viewer, 'admin')->put(route('admins.update', $admin), ['email' => 'x@medsystem.test'])->assertForbidden();
    actingAs($viewer, 'admin')->delete(route('admins.destroy', $admin))->assertForbidden();

    expect($admin->fresh())->not->toBeNull();
});

it('lists the accounts with the same number of queries however many it shows', function (): void {
    // Each row asks AdminPolicy whether it may be changed, which reads the account's roles.
    $queriesFor = function (): int {
        // Assigning a role empties the permission cache: the first request fills it again.
        actingAs($this->manager, 'admin')->get(route('admins.index'))->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();
        actingAs($this->manager, 'admin')->get(route('admins.index'))->assertOk();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    };

    Admin::factory()->mainAdmin()->create();
    $withOne = $queriesFor();

    Admin::factory()->mainAdmin()->count(3)->create();
    Admin::factory()->count(3)->create();

    expect($queriesFor())->toBe($withOne);
});
