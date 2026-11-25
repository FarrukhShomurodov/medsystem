<?php

use App\Enums\AbsenceReason;
use App\Enums\AbsenceType;
use App\Enums\ReceptionStatus;
use App\Models\Admin;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffAbsence;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use App\Services\StaffService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;

/*
| Registering and editing employees: their login account, its role, schedule and files.
*/

beforeEach(function (): void {
    SchedulingFixtures::seedWeekdays();
    $this->manager = adminWithPermissions('list_staffs', 'create_staffs', 'edit_staffs', 'delete_staffs');
    $this->nurse = app(StaffPositionService::class)->store(['name' => 'Медсестра', 'permissions' => []]);
    $this->registrar = app(StaffPositionService::class)->store(['name' => 'Регистратор', 'permissions' => []]);
});

/**
 * What the staff form posts.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function staffForm(StaffPosition $position, array $overrides = []): array
{
    return [
        'name' => 'Нодира',
        'last_name' => 'Каримова',
        'middle_name' => 'Алишеровна',
        'date_birthday' => '1990-04-12',
        'gender' => 'female',
        'citizenship' => 'Узбекистан',
        'phone' => '+998901234567',
        'address' => 'Ташкент, ул. Навои, 1',
        'position_id' => $position->id,
        'speciality_id' => Specialty::factory()->create()->id,
        'department_id' => Department::factory()->create()->id,
        'category' => 'first',
        'employment_contract_number' => 'ТД-17',
        'employment_contract_date_of_signing' => '2024-01-10',
        'employment_contract_validity_period' => '3 года',
        'passport_series' => 'AA1234567',
        'INN' => '123456789',
        'SNILS' => '987654321',
        'admission_period' => 20,
        'days' => [
            ['id' => 1, 'work' => [['start_time' => '08:00', 'end_time' => '17:00']], 'reception' => [['start_time' => '09:00', 'end_time' => '12:00']]],
        ],
        'login_email' => 'nodira@medsystem.test',
        'password' => 'secret-password',
        ...$overrides,
    ];
}

it('registers an employee with a login account holding the role of the position', function (): void {
    // The role used to be created without a guard, i.e. on "web", where an admin cannot hold it.
    actingAs($this->manager, 'admin')
        ->post(route('staffs.store'), staffForm($this->nurse))
        ->assertRedirect(route('staffs.index'))
        ->assertSessionHas('success', 'Сотрудник успешно добавлен!');

    $staff = Staff::query()->where('last_name', 'Каримова')->sole();

    expect($staff->admin)
        ->email->toBe('nodira@medsystem.test')
        ->and($staff->admin->getRoleNames()->all())->toBe(['Медсестра'])
        ->and($staff->schedule()->where('type', 'reception')->value('start_time'))->toBe('09:00:00')
        ->and(Role::query()->where('guard_name', 'web')->exists())->toBeFalse();
});

it('gives the login the role of a position that had none yet, on the admin guard', function (): void {
    // The role used to be created by name only, on the default "web" guard, and assigning it to the admin then failed.
    $orderly = StaffPosition::query()->create(['name' => 'Санитар']);

    actingAs($this->manager, 'admin')
        ->post(route('staffs.store'), staffForm($orderly))
        ->assertRedirect(route('staffs.index'));

    expect(Admin::query()->where('email', 'nodira@medsystem.test')->sole()->roles->map->only('name', 'guard_name')->all())
        ->toBe([['name' => 'Санитар', 'guard_name' => Admin::GUARD]])
        ->and($orderly->fresh()->role->name)->toBe('Санитар');
});

it('replaces the role of the old position when an employee changes position, keeping other roles', function (): void {
    // Regression: assignRole() added the new role, so a moved nurse kept the nurse permissions too.
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $staff->admin->assignRole(Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD));
    // Only a main administrator may edit the login of another one (AdminPolicy).
    $this->manager->assignRole(Admin::MAIN_ADMIN_ROLE);

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->registrar, ['password' => null]))
        ->assertRedirect(route('staffs.index'));

    expect($staff->admin->fresh()->getRoleNames()->sort()->values()->all())->toBe([Admin::MAIN_ADMIN_ROLE, 'Регистратор']);
});

it('requires a password for a new login account instead of failing on the database', function (): void {
    actingAs($this->manager, 'admin')
        ->post(route('staffs.store'), staffForm($this->nurse, ['password' => null]))
        ->assertSessionHasErrors('password');

    expect(Staff::query()->exists())->toBeFalse();
});

it('validates the appointment length and the schedule', function (array $overrides, string $field): void {
    // Regression: an appointment length of 0 made the availability endpoints loop forever.
    actingAs($this->manager, 'admin')
        ->post(route('staffs.store'), staffForm($this->nurse, $overrides))
        ->assertSessionHasErrors($field);
})->with([
    'zero minutes' => [['admission_period' => 0], 'admission_period'],
    'negative minutes' => [['admission_period' => -20], 'admission_period'],
    'reception hours without a length' => [['admission_period' => null], 'admission_period'],
    'hours ending before they start' => [[
        'days' => [['id' => 1, 'reception' => [['start_time' => '12:00', 'end_time' => '09:00']]]],
    ], 'days.0.reception.0.end_time'],
    'an unknown position' => [['position_id' => 999999], 'position_id'],
    // The text of the range picker was stored as it came: 2026-02-30 became 2 March, and a period
    // ending before it starts covered no day, so the employee stayed bookable while away.
    'a day missing from the calendar' => [['absence' => [['type' => 'day', 'date' => '2026-02-30', 'reason' => 'day_off']]], 'absence.0.start_date'],
    'a period ending before it starts' => [['absence' => [['type' => 'period', 'date' => '2026-12-10 to 2026-12-01', 'reason' => 'vacation']]], 'absence.0.end_date'],
    'an absence that is not a date' => [['absence' => [['type' => 'day', 'date' => 'завтра', 'reason' => 'day_off']]], 'absence.0.date'],
]);

it('stores a day off and a vacation period picked in the absence calendar', function (): void {
    actingAs($this->manager, 'admin')
        ->post(route('staffs.store'), staffForm($this->nurse, ['absence' => [
            ['type' => 'day', 'date' => '2026-12-04', 'reason' => 'day_off'],
            ['type' => 'period', 'date' => '2026-12-14 to 2026-12-25', 'reason' => 'vacation'],
            ['type' => 'period', 'date' => '2026-12-30', 'reason' => 'sick'],
        ]]))
        ->assertRedirect(route('staffs.index'));

    $absences = Staff::query()->where('last_name', 'Каримова')->sole()->absence()->orderBy('start_date')->get();

    expect($absences->map(fn (StaffAbsence $absence): array => [$absence->start_date->toDateString(), $absence->end_date?->toDateString()])->all())
        ->toBe([['2026-12-04', null], ['2026-12-14', '2026-12-25'], ['2026-12-30', '2026-12-30']])
        ->and($absences->map(fn (StaffAbsence $absence): array => [$absence->type, $absence->reason])->all())
        ->toBe([
            [AbsenceType::Day, AbsenceReason::DayOff],
            [AbsenceType::Period, AbsenceReason::Vacation],
            [AbsenceType::Period, AbsenceReason::Sick],
        ]);
});

it('leaves the gender of a new employee to be chosen', function (): void {
    // The select had no placeholder, so every new employee defaulted to "Мужской".
    actingAs($this->manager, 'admin')
        ->get(route('staffs.create'))
        ->assertOk()
        ->assertSeeHtml('<option value="" disabled selected>Выберите пол</option>')
        ->assertDontSeeHtml('<option value="male" selected>');
});

it('shows the saved gender, category and absences selected on the edit form', function (): void {
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse, ['absence' => [
        ['type' => 'period', 'date' => '2026-12-14 to 2026-12-25', 'reason' => 'vacation'],
    ]]));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();

    actingAs($this->manager, 'admin')
        ->get(route('staffs.edit', $staff))
        ->assertOk()
        ->assertSeeHtml('<option value="female" selected>')
        ->assertSeeHtml('<option value="first" selected>')
        ->assertSeeHtml('<option value="period" selected>')
        ->assertSeeHtml('<option value="vacation" selected>')
        ->assertSeeHtml('value="2026-12-14 to 2026-12-25"');

    actingAs($this->manager, 'admin')
        ->get(route('staffs.show', $staff))
        ->assertSeeText('Пол: Женский')
        ->assertSeeText('Категория: Первая');
});

it('drops roles left from earlier positions when the employee is saved, keeping main_admin', function (): void {
    // Renaming a position used to leave its old role on its staff. Linked to no position, they
    // survived every later position change and kept their permissions.
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $staff->admin->assignRole(Role::findOrCreate('Врач (до переименования)', Admin::GUARD), Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD));
    $this->manager->assignRole(Admin::MAIN_ADMIN_ROLE);

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->registrar, ['password' => null]))
        ->assertRedirect(route('staffs.index'));

    expect($staff->admin->fresh()->getRoleNames()->sort()->values()->all())->toBe([Admin::MAIN_ADMIN_ROLE, 'Регистратор']);
});

it('opens and saves an employee without a login account, creating the account', function (): void {
    // staffs.show and staffs.edit used to read $staff->admin->email and crashed for such staff.
    $staff = Staff::factory()->nurse()->create();

    actingAs($this->manager, 'admin')->get(route('staffs.show', $staff))->assertOk()->assertSee('нет учётной записи');
    actingAs($this->manager, 'admin')->get(route('staffs.edit', $staff))->assertOk();

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->nurse, ['password' => null]))
        ->assertSessionHasErrors('password');

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->nurse))
        ->assertRedirect(route('staffs.index'));

    expect($staff->fresh()->admin->getRoleNames()->all())->toBe(['Медсестра']);
});

it('preselects the saved professional data on the edit form', function (): void {
    // The form compared with attributes Staff does not have, so saving it reset specialty and department.
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse));
    $staff = Staff::query()->where('last_name', 'Каримова')->with('professionalData')->sole();
    Specialty::factory()->create(['name' => 'Ааа первая в списке']);

    actingAs($this->manager, 'admin')
        ->get(route('staffs.edit', $staff))
        ->assertOk()
        ->assertSeeHtml('value="'.$staff->professionalData->speciality_id.'" selected')
        ->assertSeeHtml('value="'.$staff->professionalData->department_id.'" selected')
        ->assertSeeHtml('value="09:00"');
});

it('deletes the replaced photo and signature once the change is saved', function (): void {
    // Regression: the old file was looked up by the new upload's path and never deleted.
    Storage::fake('public');
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse, [
        'avatar_url' => UploadedFile::fake()->image('old.jpg'),
        'sign_url' => UploadedFile::fake()->image('sign.png'),
    ]));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $oldAvatar = $staff->avatar_url;

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->nurse, [
            'password' => null,
            'avatar_url' => UploadedFile::fake()->image('new.jpg'),
        ]))
        ->assertRedirect(route('staffs.index'));

    $staff->refresh();

    expect($staff->avatar_url)->not->toBe($oldAvatar)->toStartWith('staff_photo/');
    Storage::disk('public')->assertMissing($oldAvatar);
    Storage::disk('public')->assertExists([$staff->avatar_url, $staff->sign_url]);
});

it('deletes an employee with their login account and files, but not the signed-in one', function (): void {
    Storage::fake('public');
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse, [
        'avatar_url' => UploadedFile::fake()->image('photo.jpg'),
    ]));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $avatar = $staff->avatar_url;

    actingAs($staff->admin, 'admin');
    $staff->admin->givePermissionTo('delete_staffs');

    actingAs($staff->admin->fresh(), 'admin')
        ->from(route('staffs.index'))
        ->delete(route('staffs.destroy', $staff))
        ->assertSessionHas('error');

    actingAs($this->manager, 'admin')
        ->delete(route('staffs.destroy', $staff))
        ->assertRedirect(route('staffs.index'));

    expect(Staff::query()->whereKey($staff->id)->exists())->toBeFalse()
        ->and(Admin::query()->where('email', 'nodira@medsystem.test')->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($avatar);
});

it('lets only a main administrator edit or delete the employee record of a main administrator', function (): void {
    // The staff form edits the e-mail, password and role of the employee's login: edit_staffs
    // was enough to take over a main administrator's account, delete_staffs to delete it.
    $staff = Staff::factory()->nurse()->create();
    $mainAdmin = Admin::factory()->mainAdmin()->forStaff($staff)->create(['email' => 'chief@medsystem.test']);
    $takeOver = staffForm($this->nurse, ['login_email' => 'taken-over@medsystem.test', 'password' => 'attacker-password']);

    actingAs($this->manager, 'admin')->get(route('staffs.edit', $staff))->assertForbidden();
    actingAs($this->manager, 'admin')->put(route('staffs.update', $staff), $takeOver)->assertForbidden();
    actingAs($this->manager, 'admin')->delete(route('staffs.destroy', $staff))->assertForbidden();

    expect($mainAdmin->fresh()->email)->toBe('chief@medsystem.test')
        ->and(Hash::check('password', $mainAdmin->fresh()->password))->toBeTrue()
        ->and($staff->fresh())->not->toBeNull();

    $this->manager->assignRole(Admin::MAIN_ADMIN_ROLE);

    actingAs($this->manager, 'admin')->get(route('staffs.edit', $staff))->assertOk();
    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->nurse, ['login_email' => 'chief2@medsystem.test', 'password' => null]))
        ->assertRedirect(route('staffs.index'));
    expect($mainAdmin->fresh()->email)->toBe('chief2@medsystem.test');

    actingAs($this->manager, 'admin')
        ->delete(route('staffs.destroy', $staff))
        ->assertRedirect(route('staffs.index'));
    expect($mainAdmin->fresh())->toBeNull();
});

it('refuses to dismiss the last main administrator who can sign in', function (): void {
    // A dismissed employee's login stops working: dismissing the last main administrator would
    // lock everybody out of the account management.
    $staff = Staff::factory()->nurse()->create();
    $this->manager->update(['staff_id' => $staff->id]);
    $this->manager->assignRole(Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD));
    $dismissal = staffForm($this->nurse, [
        'login_email' => $this->manager->email,
        'password' => null,
        'dismissal_reception_number' => 'П-12',
        'dismissal_reception_date' => '2026-11-18',
    ]);

    actingAs($this->manager, 'admin')
        ->from(route('staffs.edit', $staff))
        ->put(route('staffs.update', $staff), $dismissal)
        ->assertRedirect(route('staffs.edit', $staff))
        ->assertSessionHas('error', 'Нельзя уволить последнего главного администратора: его учётная запись перестанет работать.');

    expect($staff->document->fresh()->is_work)->toBeTrue();

    Admin::factory()->mainAdmin()->create();

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), $dismissal)
        ->assertRedirect(route('staffs.index'));

    expect($staff->document->fresh()->is_work)->toBeFalse()
        ->and($this->manager->fresh()->isActive())->toBeFalse();
});

it('registers a former employee entered with a dismissal date as dismissed, without a working login', function (): void {
    // Only an edit used to record the dismissal: the new record stayed employed, bookable and able to sign in.
    actingAs($this->manager, 'admin')
        ->post(route('staffs.store'), staffForm($this->nurse, [
            'dismissal_reception_number' => 'П-7',
            'dismissal_reception_date' => '2025-06-30',
        ]))
        ->assertRedirect(route('staffs.index'));

    $staff = Staff::query()->where('last_name', 'Каримова')->sole();

    expect($staff->isDismissed())->toBeTrue()
        ->and($staff->admin->isActive())->toBeFalse()
        ->and(Staff::query()->employed()->whereKey($staff->id)->exists())->toBeFalse();
});

it('lists the staff with today\'s status in a fixed number of queries', function (): void {
    Staff::factory()->count(2)->nurse()->create();
    SchedulingFixtures::doctor();

    $queriesOfIndex = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        actingAs($this->manager, 'admin')->get(route('staffs.index'))->assertOk()->assertSee('На работе');
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    };

    $queriesOfIndex(); // warms the permission cache
    $fewStaff = $queriesOfIndex();
    Staff::factory()->count(5)->nurse()->create();

    expect($queriesOfIndex())->toBe($fewStaff);
});

it('forbids editing staff without edit_staffs', function (): void {
    $staff = Staff::factory()->nurse()->create();

    actingAs(adminWithPermissions('list_staffs'), 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->nurse))
        ->assertForbidden();

    expect($staff->fresh()->last_name)->not->toBe('Каримова');
});

it('ends the open session of an employee whose password the staff form changes', function (): void {
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $staff->admin->givePermissionTo('list_staffs');
    Auth::forgetGuards();

    $this->post('/admin/login', ['email' => 'nodira@medsystem.test', 'password' => 'secret-password'])->assertSessionHasNoErrors();
    $this->get(route('staffs.index'))->assertOk();

    app(StaffService::class)->update($staff->fresh(), staffForm($this->nurse, ['password' => 'a-new-password']));
    Auth::forgetGuards();

    $this->get(route('staffs.index'))->assertRedirectToRoute('dashboard.login');
    $this->assertGuest('admin');
});

it('keeps signed in an employee who changes their own password on the staff form', function (): void {
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $staff->admin->givePermissionTo(['list_staffs', 'edit_staffs']);
    Auth::forgetGuards();

    $this->post('/admin/login', ['email' => 'nodira@medsystem.test', 'password' => 'secret-password'])->assertSessionHasNoErrors();

    $this->put(route('staffs.update', $staff), staffForm($this->nurse, ['password' => 'a-new-password']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('staffs.index'));
    Auth::forgetGuards();

    $this->get(route('staffs.index'))->assertOk();
    $this->assertAuthenticatedAs($staff->admin->fresh(), 'admin');
});

it('saves an absence over booked appointments and lists them for rescheduling', function (): void {
    // The absence hid the appointments from both calendars, and nobody was told about them.
    $service = Service::factory()->appointment()->create();
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse, ['services' => [$service->id]]));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $monday = CarbonImmutable::parse('next monday');
    $patient = Patient::factory()->create();
    $reception = Reception::factory()->for($staff)->for($service)->for($patient)->create([
        'date' => $monday->toDateString(), 'start_time' => '09:00', 'end_time' => '09:20',
    ]);
    $form = staffForm($this->nurse, ['services' => [$service->id], 'password' => null]);

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), $form)
        ->assertRedirect(route('staffs.index'))
        ->assertSessionMissing('warning');

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), [...$form, 'absence' => [['type' => 'day', 'date' => $monday->toDateString(), 'reason' => 'sick']]])
        ->assertRedirect(route('staffs.index'))
        ->assertSessionHas('success', 'Сотрудник успешно обновлён!')
        ->assertSessionHas('warning', "Записи вне графика сотрудника, перенесите или отмените их: {$monday->format('d.m.Y')} 09:00, {$patient->full_name}.");

    expect($staff->absence()->count())->toBe(1)
        ->and($reception->fresh()->status)->toBe(ReceptionStatus::New);
});

it('lists the appointments left outside changed reception hours or kept by a dismissed employee', function (array $change): void {
    $service = Service::factory()->appointment()->create();
    actingAs($this->manager, 'admin')->post(route('staffs.store'), staffForm($this->nurse, ['services' => [$service->id]]));
    $staff = Staff::query()->where('last_name', 'Каримова')->sole();
    $monday = CarbonImmutable::parse('next monday');
    Reception::factory()->for($staff)->for($service)->create(['date' => $monday->toDateString(), 'start_time' => '11:40', 'end_time' => '12:00']);
    // A cancelled appointment needs nothing.
    Reception::factory()->for($staff)->for($service)->create(['date' => $monday->toDateString(), 'start_time' => '11:00', 'end_time' => '11:20', 'status' => ReceptionStatus::Cancelled]);

    actingAs($this->manager, 'admin')
        ->put(route('staffs.update', $staff), staffForm($this->nurse, ['services' => [$service->id], 'password' => null, ...$change]))
        ->assertRedirect(route('staffs.index'))
        ->assertSessionHas('warning', fn (string $warning): bool => str_contains($warning, "{$monday->format('d.m.Y')} 11:40") && ! str_contains($warning, '11:00'));
})->with([
    'shorter reception hours' => [['days' => [['id' => 1, 'work' => [['start_time' => '08:00', 'end_time' => '17:00']], 'reception' => [['start_time' => '09:00', 'end_time' => '11:00']]]]]],
    'no reception hours on that weekday' => [['days' => [['id' => 2, 'work' => [['start_time' => '08:00', 'end_time' => '17:00']], 'reception' => [['start_time' => '09:00', 'end_time' => '12:00']]]]]],
    'the service no longer offered' => [['services' => []]],
    'a dismissal' => [['dismissal_reception_date' => '2026-11-01']],
]);
