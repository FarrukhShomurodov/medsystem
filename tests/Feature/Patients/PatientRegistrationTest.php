<?php

use App\Models\Admin;
use App\Models\City;
use App\Models\File;
use App\Models\Patient;
use App\Models\Region;
use App\Models\Staff;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\StaffPositionSeeder;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/**
 * The required fields of the registration form, plus the given ones.
 *
 * @param  array<string, mixed>  $fields
 * @return array<string, mixed>
 */
function patientForm(array $fields = []): array
{
    return [
        'first_name' => 'Алия',
        'last_name' => 'Каримова',
        'birth_date' => '1988-03-14',
        'gender' => 'female',
        'citizenship' => 'Узбекистан',
        'passport_series' => 'AB1234567',
        'passport_validity_period' => now()->addYears(5)->toDateString(),
        'phone' => '+998 90 123-45-67',
        ...$fields,
    ];
}

function registeredPatient(): Patient
{
    return Patient::query()->where('passport_series', 'AB1234567')->sole();
}

it('creates the region and city typed into the address selects, named as typed', function (): void {
    // The registration city was created with the name of the residence city (city_id), e.g. "15".
    actingAs(adminWithPermissions('create_patients', 'view_patients'), 'admin')
        ->post(route('patients.store'), patientForm([
            'region_id' => 'Самаркандская область',
            'city_id' => 'Самарканд',
            'registered_region_id' => 'Бухарская область',
            'registered_city_id' => 'Бухара',
        ]))
        ->assertSessionHasNoErrors();

    $patient = registeredPatient()->load('region', 'city', 'registeredRegion', 'registeredCity');

    expect($patient->region->name)->toBe('Самаркандская область')
        ->and($patient->city->name)->toBe('Самарканд')
        ->and($patient->city->region_id)->toBe($patient->region_id)
        ->and($patient->registeredRegion->name)->toBe('Бухарская область')
        ->and($patient->registeredCity->name)->toBe('Бухара')
        ->and($patient->registeredCity->region_id)->toBe($patient->registered_region_id);
});

it('reuses a typed region or city that already exists instead of creating a duplicate', function (): void {
    $region = Region::factory()->create(['name' => 'Ташкентская область']);
    $city = City::factory()->create(['name' => 'Чирчик', 'region_id' => $region->id]);

    actingAs(adminWithPermissions('create_patients', 'view_patients'), 'admin')
        ->post(route('patients.store'), patientForm([
            'region_id' => 'Ташкентская область',
            'city_id' => 'Чирчик',
            'registered_region_id' => (string) $region->id,
            'registered_city_id' => (string) $city->id,
        ]))
        ->assertSessionHasNoErrors();

    expect(registeredPatient()->only('region_id', 'city_id', 'registered_region_id', 'registered_city_id'))
        ->toBe(['region_id' => $region->id, 'city_id' => $city->id, 'registered_region_id' => $region->id, 'registered_city_id' => $city->id])
        ->and(Region::query()->count())->toBe(1)
        ->and(City::query()->count())->toBe(1);
});

it('rejects a region or city id that does not exist', function (): void {
    // Unknown ids reached the database and failed on the foreign key: a 500.
    actingAs(adminWithPermissions('create_patients'), 'admin')
        ->post(route('patients.store'), patientForm(['region_id' => '999999', 'registered_city_id' => '999999']))
        ->assertSessionHasErrors(['region_id', 'registered_city_id']);

    expect(Patient::query()->count())->toBe(0);
});

it('asks for the region of a newly typed city', function (): void {
    actingAs(adminWithPermissions('create_patients'), 'admin')
        ->post(route('patients.store'), patientForm(['city_id' => 'Нукус']))
        ->assertSessionHasErrors('city_id');

    expect(City::query()->count())->toBe(0);
});

it('takes the registrar to the new patient\'s card', function (): void {
    // The registrar may open patient cards but not the patient list it used to redirect to (a 403).
    seed(PermissionSeeder::class);
    $registrar = Admin::factory()->role('Регистратор')->create();

    actingAs($registrar, 'admin')
        ->post(route('patients.store'), patientForm())
        ->assertRedirectToRoute('patients.show', registeredPatient())
        ->assertSessionHas('success', 'Пациент успешно зарегистрирован.');

    actingAs($registrar, 'admin')->get(route('patients.show', registeredPatient()))->assertOk();
});

it('refuses a date of birth in the future', function (): void {
    actingAs(adminWithPermissions('create_patients', 'view_patients'), 'admin')
        ->post(route('patients.store'), patientForm(['birth_date' => today()->addDay()->toDateString()]))
        ->assertSessionHasErrors('birth_date');

    expect(Patient::query()->count())->toBe(0);
});

it('forbids registering a patient without create_patients', function (): void {
    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->post(route('patients.store'), patientForm())
        ->assertForbidden();

    expect(Patient::query()->count())->toBe(0);
});

it('gives the front desk the short registration form and everyone else the full one', function (): void {
    // The form was chosen by the role name "Регистратор"; it now follows the employee's position.
    seed(StaffPositionSeeder::class);
    $account = fn (Staff $employee): Admin => tap(adminWithPermissions('create_patients'))->update(['staff_id' => $employee->id]);

    actingAs($account(Staff::factory()->withPosition('Регистратор')->create()), 'admin')
        ->get(route('patients.create'))
        ->assertViewIs('admin.patients.register');

    actingAs($account(Staff::factory()->doctor()->create()), 'admin')
        ->get(route('patients.create'))
        ->assertViewIs('admin.patients.create');

    actingAs(adminWithPermissions('create_patients'), 'admin')
        ->get(route('patients.create'))
        ->assertViewIs('admin.patients.create');
});

it('keeps the registration city selected when the form comes back with errors', function (): void {
    // The options compared the region left over from the region loop with the region field.
    $city = City::factory()->create();

    actingAs(adminWithPermissions('create_patients'), 'admin')
        ->from(route('patients.create'))
        ->post(route('patients.store'), patientForm(['first_name' => '', 'registered_city_id' => $city->id]))
        ->assertSessionHasErrors('first_name');

    $this->get(route('patients.create'))
        ->assertOk()
        ->assertSeeHtml('value="'.$city->id.'" selected');
});

it('leaves the gender of a new patient to be chosen', function (): void {
    // The disabled placeholder was not selected, so the browser picked the first real option:
    // a patient registered without touching the field was saved as a man, past "required".
    actingAs(adminWithPermissions('create_patients'), 'admin')
        ->get(route('patients.create'))
        ->assertOk()
        ->assertSeeHtml('<option value="" disabled selected>Выберите пол</option>')
        ->assertDontSeeHtml('<option value="male" selected>');

    $this->post(route('patients.store'), patientForm(['gender' => null]))->assertSessionHasErrors('gender');
});

it('keeps the gender of the patient selected on the edit form', function (): void {
    // The options compared old('gender') with the missing attributes $patient->male / $patient->female,
    // so nothing was selected and saving the form turned every patient into a man.
    $patient = Patient::factory()->create(['gender' => 'female']);

    actingAs(adminWithPermissions('edit_patients'), 'admin')
        ->get(route('patients.edit', $patient))
        ->assertOk()
        ->assertSeeHtml('<option value="female" selected>')
        ->assertDontSeeHtml('<option value="male" selected>');
});

it('saves the patient and returns to the card with a message about the patient', function (): void {
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('edit_patients', 'view_patients'), 'admin')
        ->put(route('patients.update', $patient), patientForm(['first_name' => 'Лола']))
        ->assertRedirectToRoute('patients.show', $patient)
        ->assertSessionHas('success', 'Данные пациента успешно обновлены.');

    expect($patient->fresh()->first_name)->toBe('Лола');
});

it('keeps the address of a patient when the form leaves the selects empty', function (): void {
    $patient = Patient::factory()->create(['region_id' => Region::factory()->create()->id]);

    actingAs(adminWithPermissions('edit_patients'), 'admin')
        ->put(route('patients.update', $patient), patientForm())
        ->assertSessionHasNoErrors();

    expect($patient->fresh()->region_id)->toBe($patient->region_id);
});

it('takes an account that may open neither the card nor the list back to its form', function (): void {
    // The redirect went to the patient list, a 403 right after a successful save.
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('edit_patients'), 'admin')
        ->from(route('patients.edit', $patient))
        ->put(route('patients.update', $patient), patientForm())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('patients.edit', $patient));

    actingAs(adminWithPermissions('edit_patients', 'list_patients'), 'admin')
        ->put(route('patients.update', $patient), patientForm())
        ->assertRedirectToRoute('patients.index');
});

it('deletes the patient together with the attached documents', function (): void {
    Storage::fake(File::DISK);
    $patient = Patient::factory()->create();
    Storage::disk(File::DISK)->put('patient_files/scan.pdf', '%PDF-1.4');
    $patient->files()->create(['name' => 'scan.pdf', 'url' => 'patient_files/scan.pdf']);

    actingAs(adminWithPermissions('delete_patients', 'list_patients'), 'admin')
        ->delete(route('patients.destroy', $patient))
        ->assertRedirectToRoute('patients.index')
        ->assertSessionHas('success', 'Пациент успешно удалён.');

    expect(Patient::query()->whereKey($patient->id)->exists())->toBeFalse()
        ->and(File::query()->count())->toBe(0);
    Storage::disk(File::DISK)->assertMissing('patient_files/scan.pdf');
});

it('rejects a postcode the database cannot hold', function (): void {
    // The rule was "int": a longer number passed and the integer column refused it, a 500.
    actingAs(adminWithPermissions('create_patients', 'view_patients'), 'admin')
        ->post(route('patients.store'), patientForm(['residential_postcode' => '3000000000', 'registered_postcode' => '-100']))
        ->assertSessionHasErrors(['residential_postcode', 'registered_postcode']);

    expect(Patient::query()->count())->toBe(0);
});
