<?php

use App\Imports\ServicesImport;
use App\Models\City;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Manufacturer;
use App\Models\Office;
use App\Models\Patient;
use App\Models\PatientAssignment;
use App\Models\PatientDiagnosis;
use App\Models\Procedure;
use App\Models\Region;
use App\Models\RoomBed;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceSubGroup;
use App\Models\ServiceType;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffProfessionalData;
use App\Models\Survey;
use App\Models\SurveyType;
use App\Models\WardRoom;
use Database\Seeders\ServiceTypeSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| The reference catalogues of the clinical module and the small patient-card forms that pick from them.
*/

/**
 * @return array<string, mixed>
 */
function serviceForm(array $overrides = []): array
{
    return [
        'name' => 'Консультация невролога',
        'price' => '112000',
        'currency' => 'UZS',
        'service_group_id' => ServiceGroup::factory()->create()->id,
        'service_type_id' => ServiceType::factory()->create()->id,
        'is_active' => '1',
        'is_appointment' => '1',
        ...$overrides,
    ];
}

describe('departments', function (): void {
    it('opens the edit form of a department', function (): void {
        $department = Department::factory()->create();
        $department->rooms()->attach(WardRoom::factory()->create(['room_number' => '204']));
        $professional = StaffProfessionalData::factory()->create(['department_id' => $department->id]);
        $staffName = $professional->staff->full_name;

        // The floor select read the undefined $office.
        actingAs(adminWithPermissions('edit_departments'), 'admin')
            ->get(route('departments.edit', $department))
            ->assertOk()
            ->assertSee('204')
            ->assertSeeText($staffName)
            ->assertDontSee(route('staffs.show', $professional->staff_id));

        // The staff record is linked only for accounts that may open it.
        actingAs(adminWithPermissions('edit_departments', 'list_staffs'), 'admin')
            ->get(route('departments.edit', $department))
            ->assertOk()
            ->assertSee(route('staffs.show', $professional->staff_id));
    });

    it('lists departments without a floor', function (): void {
        Department::factory()->create(['name' => 'Приёмное отделение', 'floor_id' => null]);
        actingAs(adminWithPermissions('list_departments'), 'admin');

        $this->get(route('departments.index'))->assertOk()->assertSee('Приёмное отделение');
    });

    it('saves a department with its rooms and refuses unknown rooms', function (): void {
        $rooms = WardRoom::factory()->count(2)->create();
        actingAs(adminWithPermissions('create_departments'), 'admin');

        $this->post(route('departments.store'), ['name' => 'Кардиология', 'rooms' => [999_999]])
            ->assertSessionHasErrors('rooms.0');

        $this->post(route('departments.store'), ['name' => 'Кардиология', 'rooms' => $rooms->modelKeys()])
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'Отделение успешно добавлено!');

        expect(Department::query()->sole()->rooms()->count())->toBe(2);
    });
});

/**
 * The child rows of a record, as the edit form posts them back: [[id, value], ...].
 *
 * @param  list<array{0: int|string|null, 1: mixed}>  $rows
 * @return list<array<string, mixed>>
 */
function childRows(string $field, array $rows): array
{
    return array_map(fn (array $row): array => ['id' => $row[0], $field => $row[1]], $rows);
}

describe('ward rooms and their beds', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_department_rooms', 'create_department_rooms', 'edit_department_rooms', 'delete_department_rooms'), 'admin');
    });

    /** @return list<string> */
    $beds = fn (WardRoom $room): array => $room->beds()->orderBy('id')->pluck('bed_number')->all();

    it('creates a room with its beds', function () use ($beds): void {
        $this->post(route('departments.room.store'), ['room_number' => '101', 'bed_numbers' => ['1', '2']])
            ->assertRedirect(route('departments.room.index'))
            ->assertSessionHas('success', 'Палата успешно добавлена!');

        expect($beds(WardRoom::query()->sole()))->toBe(['1', '2']);
    });

    it('renames, keeps, removes and adds beds in one save', function () use ($beds): void {
        $room = WardRoom::factory()->create();
        [$renamed, $kept] = [$room->beds()->create(['bed_number' => '1']), $room->beds()->create(['bed_number' => '2'])];
        $room->beds()->create(['bed_number' => '3']);

        $this->put(route('departments.room.update', $room), [
            'room_number' => '102',
            'bed_numbers' => childRows('bed_number', [[$renamed->id, '1A'], [$kept->id, '2'], [null, '4']]),
        ])->assertRedirect(route('departments.room.index'))->assertSessionHas('success', 'Палата успешно обновлена!');

        expect($beds($room))->toBe(['1A', '2', '4'])
            ->and($room->fresh()->room_number)->toBe('102');
    });

    it('refuses a bed that is gone or belongs to another room, and changes nothing', function (string $stale) use ($beds): void {
        // The save reported success and dropped the bed: updateOrCreate made a new row for the
        // unknown id, and the clean-up deleted it again because the posted id was the one kept.
        $room = WardRoom::factory()->create(['room_number' => '101']);
        $kept = $room->beds()->create(['bed_number' => '1']);
        $other = WardRoom::factory()->create();
        $foreign = $other->beds()->create(['bed_number' => '9']);
        $gone = $room->beds()->create(['bed_number' => '2']);
        $gone->delete();

        $this->from(route('departments.room.edit', $room))
            ->put(route('departments.room.update', $room), [
                'room_number' => '101',
                'bed_numbers' => childRows('bed_number', [[$kept->id, '1'], [$stale === 'gone' ? $gone->id : $foreign->id, '2']]),
            ])
            ->assertRedirect(route('departments.room.edit', $room))
            ->assertSessionHasErrors(['bed_numbers.1.id' => 'Койка уже удалена или относится к другой палате. Обновите страницу.']);

        expect($beds($room))->toBe(['1'])
            ->and($beds($other))->toBe(['9']);
    })->with(['deleted since the form was opened' => 'gone', 'a bed of another room' => 'foreign']);

    it('refuses the beds of a new room that are blank, too long or not numbers', function (array $form, string $field): void {
        // A space passed the browser's required, became null and hit the NOT NULL column: a 500.
        $this->from(route('departments.room.create'))
            ->post(route('departments.room.store'), $form)
            ->assertRedirect(route('departments.room.create'))
            ->assertSessionHasErrors($field);

        expect(WardRoom::query()->count())->toBe(0);
    })->with([
        'a space' => [['room_number' => '101', 'bed_numbers' => [' ']], 'bed_numbers.0'],
        'too long' => [['room_number' => '101', 'bed_numbers' => [str_repeat('1', 256)]], 'bed_numbers.0'],
        'a room number too long' => [['room_number' => str_repeat('1', 256), 'bed_numbers' => ['1']], 'room_number'],
        'a row instead of a number' => [['room_number' => '101', 'bed_numbers' => [['a']]], 'bed_numbers.0'],
        'twice the same number' => [['room_number' => '101', 'bed_numbers' => ['1', '1']], 'bed_numbers.1'],
    ]);

    it('refuses the beds of an edited room that are blank, too long or malformed', function (array $rows, string $field) use ($beds): void {
        $room = WardRoom::factory()->create();
        $bed = $room->beds()->create(['bed_number' => '1']);
        // "BED" stands for the id of the room's bed.
        $posted = array_map(fn (mixed $row): mixed => is_array($row) && ($row['id'] ?? null) === 'BED' ? ['id' => $bed->id] + $row : $row, $rows);

        $this->from(route('departments.room.edit', $room))
            ->put(route('departments.room.update', $room), ['room_number' => '101', 'bed_numbers' => $posted])
            ->assertRedirect(route('departments.room.edit', $room))
            ->assertSessionHasErrors($field);

        expect($beds($room))->toBe(['1']);
    })->with([
        'a space in a bed' => [[['id' => 'BED', 'bed_number' => ' ']], 'bed_numbers.0.bed_number'],
        'a space in a new bed' => [[['id' => 'BED', 'bed_number' => '1'], ['id' => null, 'bed_number' => ' ']], 'bed_numbers.1.bed_number'],
        'too long' => [[['id' => 'BED', 'bed_number' => str_repeat('1', 256)]], 'bed_numbers.0.bed_number'],
        'a row without its number' => [[['id' => '']], 'bed_numbers.0.bed_number'],
        'a number instead of a row' => [['1', '2'], 'bed_numbers.0'],
        'an id that is not one' => [[['id' => 'abc', 'bed_number' => '1']], 'bed_numbers.0.id'],
        'one bed twice' => [[['id' => 'BED', 'bed_number' => '1'], ['id' => 'BED', 'bed_number' => '2']], 'bed_numbers.1.id'],
    ]);
    it('shows the error of a bed on the form', function (): void {
        $room = WardRoom::factory()->create();

        $this->from(route('departments.room.edit', $room))
            ->followingRedirects()
            ->put(route('departments.room.update', $room), ['room_number' => '101', 'bed_numbers' => childRows('bed_number', [[null, ' ']])])
            ->assertOk()
            ->assertSee('Поле номер койки обязательно для заполнения.');
    });

    it('deletes a room with its beds', function (): void {
        $room = WardRoom::factory()->create();
        $room->beds()->create(['bed_number' => '1']);

        $this->delete(route('departments.room.destroy', $room))->assertRedirect(route('departments.room.index'));

        expect(WardRoom::query()->count())->toBe(0)
            ->and(RoomBed::query()->count())->toBe(0);
    });
});

describe('service groups and their subgroups', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_service_groups', 'create_service_groups', 'edit_service_groups', 'delete_service_groups'), 'admin');
    });

    /** @return list<string> */
    $subGroups = fn (ServiceGroup $group): array => $group->subGroups()->orderBy('id')->pluck('name')->all();

    it('creates a group with its subgroups', function () use ($subGroups): void {
        $this->post(route('service.groups.store'), ['name' => 'Диагностика', 'sub_groups' => ['УЗИ', 'Рентген']])
            ->assertRedirect(route('service.groups.index'))
            ->assertSessionHas('success', 'Группа успешно добавлена!');

        expect($subGroups(ServiceGroup::query()->sole()))->toBe(['УЗИ', 'Рентген']);
    });

    it('renames, keeps, removes and adds subgroups in one save', function () use ($subGroups): void {
        $group = ServiceGroup::factory()->create();
        [$renamed, $kept] = [$group->subGroups()->create(['name' => 'УЗИ']), $group->subGroups()->create(['name' => 'МРТ'])];
        $group->subGroups()->create(['name' => 'КТ']);

        $this->put(route('service.groups.update', $group), [
            'name' => 'Лучевая диагностика',
            'sub_groups' => childRows('name', [[$renamed->id, 'УЗИ органов'], [$kept->id, 'МРТ'], [null, 'Рентген']]),
        ])->assertRedirect(route('service.groups.index'))->assertSessionHas('success', 'Группа успешно обновлена!');

        expect($subGroups($group))->toBe(['УЗИ органов', 'МРТ', 'Рентген']);
    });

    it('refuses a subgroup that is gone or belongs to another group, and changes nothing', function () use ($subGroups): void {
        $group = ServiceGroup::factory()->create();
        $kept = $group->subGroups()->create(['name' => 'УЗИ']);
        $other = ServiceGroup::factory()->create();
        $foreign = $other->subGroups()->create(['name' => 'Анализы']);

        $this->from(route('service.groups.edit', $group))
            ->put(route('service.groups.update', $group), [
                'name' => $group->name,
                'sub_groups' => childRows('name', [[$kept->id, 'УЗИ'], [$foreign->id, 'Анализы крови']]),
            ])
            ->assertRedirect(route('service.groups.edit', $group))
            ->assertSessionHasErrors(['sub_groups.1.id' => 'Подгруппа уже удалена или относится к другой группе. Обновите страницу.']);

        expect($subGroups($group))->toBe(['УЗИ'])
            ->and($subGroups($other))->toBe(['Анализы']);
    });

    it('refuses the subgroups of a new group that are blank or too long', function (array $form, string $field): void {
        $this->from(route('service.groups.create'))
            ->post(route('service.groups.store'), $form)
            ->assertRedirect(route('service.groups.create'))
            ->assertSessionHasErrors($field);

        expect(ServiceGroup::query()->count())->toBe(0);
    })->with([
        'a space' => [['name' => 'Диагностика', 'sub_groups' => [' ']], 'sub_groups.0'],
        'too long' => [['name' => 'Диагностика', 'sub_groups' => [str_repeat('а', 256)]], 'sub_groups.0'],
        'a row instead of a name' => [['name' => 'Диагностика', 'sub_groups' => [['УЗИ']]], 'sub_groups.0'],
    ]);

    it('refuses the subgroups of an edited group that are blank or malformed', function (array $rows, string $field) use ($subGroups): void {
        $group = ServiceGroup::factory()->create();
        $group->subGroups()->create(['name' => 'УЗИ']);

        $this->from(route('service.groups.edit', $group))
            ->put(route('service.groups.update', $group), ['name' => 'Диагностика', 'sub_groups' => $rows])
            ->assertRedirect(route('service.groups.edit', $group))
            ->assertSessionHasErrors($field);

        expect($subGroups($group))->toBe(['УЗИ']);
    })->with([
        'a space in a new subgroup' => [[['id' => null, 'name' => ' ']], 'sub_groups.0.name'],
        'a row without its name' => [[['id' => '']], 'sub_groups.0.name'],
        'a name instead of a row' => [['УЗИ'], 'sub_groups.0'],
    ]);
    it('deletes a group with its subgroups', function (): void {
        $group = ServiceGroup::factory()->create();
        $group->subGroups()->create(['name' => 'УЗИ']);

        $this->delete(route('service.groups.destroy', $group))->assertRedirect(route('service.groups.index'));

        expect(ServiceGroup::query()->count())->toBe(0)
            ->and(ServiceSubGroup::query()->count())->toBe(0);
    });
});

describe('offices and floors', function (): void {
    it('lists offices without a floor and shows a floor with its departments', function (): void {
        Office::factory()->create(['name' => 'Кабинет УЗИ', 'floor_id' => null]);
        $department = Department::factory()->create(['name' => 'Хирургическое отделение']);
        actingAs(adminWithPermissions('list_offices', 'view_floors'), 'admin');

        $this->get(route('offices.index'))->assertOk()->assertSee('Кабинет УЗИ');
        $this->get(route('floors.show', $department->floor_id))->assertOk()->assertSee('Хирургическое отделение');
    });
});

describe('cities', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_cities', 'create_cities', 'edit_cities'), 'admin');
    });

    it('takes the region from the list and never creates one from typed text', function (): void {
        // The old service created a region named after any non-numeric input.
        $this->post(route('cities.store'), ['name' => 'Чирчик', 'region_id' => 'Новая область'])
            ->assertSessionHasErrors('region_id');

        expect(Region::query()->count())->toBe(0)
            ->and(City::query()->count())->toBe(0);
    });

    it('saves a city of an existing region', function (): void {
        $region = Region::factory()->create();

        $this->post(route('cities.store'), ['name' => 'Чирчик', 'region_id' => $region->id])
            ->assertRedirect(route('cities.index'))
            ->assertSessionHas('success', 'Город успешно добавлен!');

        $this->get(route('cities.index'))->assertOk()->assertSee('Чирчик')->assertSee($region->name);
    });

    it('moves a city to another region', function (): void {
        $city = City::factory()->create(['name' => 'Чирчик']);
        $region = Region::factory()->create();

        $this->put(route('cities.update', $city), ['name' => 'Чирчик', 'region_id' => $region->id])
            ->assertRedirect(route('cities.index'))
            ->assertSessionHas('success', 'Город успешно обновлен!');

        expect($city->fresh()->region_id)->toBe($region->id);
    });
});

describe('specialties', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_specialties', 'create_specialties', 'edit_specialties'), 'admin');
    });

    it('adds a specialty and renames it', function (): void {
        $this->post(route('specialties.store'), ['name' => 'Кардиолог'])
            ->assertRedirect(route('specialties.index'))
            ->assertSessionHas('success', 'Специальность успешно добавлена!');

        $specialty = Specialty::query()->where('name', 'Кардиолог')->sole();

        $this->put(route('specialties.update', $specialty), ['name' => 'Детский кардиолог'])
            ->assertRedirect(route('specialties.index'))
            ->assertSessionHas('success', 'Специальность успешно обновлена!');

        expect($specialty->fresh()->name)->toBe('Детский кардиолог');
    });

    it('refuses the name of another specialty', function (): void {
        Specialty::factory()->create(['name' => 'Кардиолог']);

        $this->post(route('specialties.store'), ['name' => 'Кардиолог'])->assertSessionHasErrors('name');

        expect(Specialty::query()->where('name', 'Кардиолог')->count())->toBe(1);
    });
});

describe('services price list', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_services', 'create_services', 'edit_services'), 'admin');
    });

    it('never creates a service type from typed text', function (): void {
        // The old service created a type named after any non-numeric service_type_id.
        $this->post(route('services.store'), serviceForm(['service_type_id' => 'Новый тип']))
            ->assertSessionHasErrors('service_type_id');

        expect(ServiceType::query()->where('name', 'Новый тип')->exists())->toBeFalse();
    });

    it('stores the price as typed: what the cash desk charges, VAT included', function (): void {
        // The form took 12 % VAT off, which the import did not do and billing never added back:
        // the same «Стоимость» typed in the form or the sheet was charged 100 000 or 112 000.
        $this->post(route('services.store'), serviceForm())->assertRedirect(route('services.index'));

        expect(Service::query()->sole()->price)->toBe('112000.00');
    });

    it('stores an edited price as typed and shows the stored one back', function (): void {
        // The edit form showed the stored price, and a changed one lost VAT again: raising
        // 100 000 to 105 000 saved 93 750.
        $service = Service::factory()->create(['price' => 100000]);
        $edit = fn (string $price) => $this->put(route('services.update', $service), serviceForm([
            'name' => 'Консультация невролога (повторная)',
            'price' => $price,
            'service_group_id' => $service->service_group_id,
            'service_type_id' => $service->service_type_id,
        ]))->assertSessionHas('success', 'Услуга успешно обновлена!');

        $this->get(route('services.edit', $service))->assertOk()->assertSeeHtml('value="100000.00"');

        $edit('105000');
        expect($service->fresh()->only(['name', 'price']))->toBe(['name' => 'Консультация невролога (повторная)', 'price' => '105000.00']);

        $this->get(route('services.edit', $service))->assertOk()->assertSeeHtml('value="105000.00"');

        $edit('105000.00');
        expect($service->fresh()->price)->toBe('105000.00');
    });

    it('stores the same price for the same «Стоимость» in the form and in the import', function (): void {
        $form = serviceForm(['name' => 'Через форму']);
        $this->post(route('services.store'), $form)->assertRedirect(route('services.index'));

        $import = new ServicesImport;
        $import->collection(collect([collect([
            'naimenovanie' => 'Из прайс-листа', 'stoimost' => 112000, 'valiuta' => 'UZS',
            'tip_id' => $form['service_type_id'], 'gruppa_id' => $form['service_group_id'],
        ])]));

        expect(Service::query()->where('name', 'Через форму')->value('price'))
            ->toBe(Service::query()->where('name', 'Из прайс-листа')->value('price'));
    });

    it('shows the import button only to accounts that may import', function (): void {
        $this->get(route('services.index'))->assertOk()->assertSee(route('services.import.show'));

        actingAs(adminWithPermissions('list_services'), 'admin');

        $this->get(route('services.index'))->assertOk()->assertDontSee(route('services.import.show'));
    });
});

describe('service types', function (): void {
    beforeEach(function (): void {
        seed(ServiceTypeSeeder::class);
    });

    it('protects the seeded types 1-21 on every write', function (): void {
        $seeded = ServiceType::query()->findOrFail(ServiceType::LAST_SYSTEM_TYPE_ID);
        actingAs(adminWithPermissions('list_service_types', 'edit_service_types', 'delete_service_types'), 'admin');

        $this->get(route('service.types.edit', $seeded))->assertRedirect(route('service.types.index'))->assertSessionHas('error');
        $this->put(route('service.types.update', $seeded), ['name' => 'Другое'])->assertSessionHas('error');
        $this->delete(route('service.types.destroy', $seeded))->assertSessionHas('error');

        expect($seeded->fresh()->name)->not->toBe('Другое');
    });

    it('edits a type added later', function (): void {
        $added = ServiceType::factory()->create();
        actingAs(adminWithPermissions('edit_service_types'), 'admin');

        $this->get(route('service.types.edit', $added))->assertOk();
        $this->put(route('service.types.update', $added), ['name' => 'Реабилитация'])
            ->assertSessionHas('success', 'Тип услуги успешно обновлен!');
    });
});

describe('procedures', function (): void {
    it('accepts a price with tiyins', function (): void {
        actingAs(adminWithPermissions('create_service_procedures'), 'admin');

        // The price rule was "int", so 15000.50 was refused.
        $this->post(route('procedures.store'), ['name' => 'Перевязка', 'price' => '15000.50'])
            ->assertRedirect(route('procedures.index'));

        expect(Procedure::query()->sole()->price)->toBe('15000.50');
    });
});

describe('diagnoses', function (): void {
    it('shows the import button only to accounts that may import', function (): void {
        actingAs(adminWithPermissions('list_diagnoses', 'create_diagnoses'), 'admin');
        $this->get(route('diagnoses.index'))->assertOk()->assertSee(route('diagnoses.import.show'));

        actingAs(adminWithPermissions('list_diagnoses'), 'admin');
        $this->get(route('diagnoses.index'))->assertOk()->assertDontSee(route('diagnoses.import.show'));
    });

    it('links surveys to an imported diagnosis with a long ICD name', function (): void {
        // A real ICD-10 name of the ministry list is 258 characters long; the 255 limit of the form
        // refused every edit of such a diagnosis, so no survey could be linked to it.
        $diagnosis = Diagnosis::factory()->create(['code' => 'Q87.8', 'name' => str_repeat('Синдром врожденных аномалий ', 9).'уточненный']);
        $survey = Survey::factory()->create();
        actingAs(adminWithPermissions('list_diagnoses', 'edit_diagnoses'), 'admin');

        $this->put(route('diagnoses.update', $diagnosis), ['code' => $diagnosis->code, 'name' => $diagnosis->name, 'surveys' => [$survey->id]])
            ->assertRedirect(route('diagnoses.index'))
            ->assertSessionHas('success', 'Диагноз успешно обновлен!');

        expect($survey->fresh()->diagnosis_id)->toBe($diagnosis->id);
    });

    it('needs a diagnosis from the catalogue for a patient diagnosis', function (): void {
        $patient = Patient::factory()->create();
        actingAs(adminWithPermissions('create_patient_diagnoses'), 'admin');

        // diagnosis_id was nullable: an empty diagnosis row was stored for the patient.
        $this->post(route('patient.diagnoses.store'), ['patient_id' => $patient->id])
            ->assertSessionHasErrors('diagnosis_id');

        expect(PatientDiagnosis::query()->count())->toBe(0);

        $diagnosis = Diagnosis::factory()->create();
        $diagnosis->surveys()->create(['title' => 'Анкета пациента с гипертензией', 'color' => '#FFD700', 'survey_type_id' => SurveyType::factory()->create()->id]);

        $this->postJson(route('patient.diagnoses.store'), ['patient_id' => $patient->id, 'diagnosis_id' => $diagnosis->id], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('surveys.0.title', 'Анкета пациента с гипертензией');
    });
});

describe('referrals from the patient card', function (): void {
    it('creates a referral without a comment and signs it with the doctor', function (): void {
        $patient = Patient::factory()->create();
        $doctor = Staff::factory()->create();
        actingAs(tap(adminWithPermissions('create_patient_services'))->update(['staff_id' => $doctor->id]), 'admin');

        // "sometimes|string" refused the empty comment textarea (null after ConvertEmptyStringsToNull).
        $this->post(route('patient.assignment.store'), ['patient_id' => $patient->id, 'service_id' => Service::factory()->create()->id, 'comment' => ''])
            ->assertRedirect(route('patients.show', $patient))
            ->assertSessionHas('success', 'Направление успешно создано!');

        expect(PatientAssignment::query()->sole()->only(['staff_id', 'comment']))->toBe(['staff_id' => $doctor->id, 'comment' => null]);
    });
});

describe('equipment', function (): void {
    it('refuses values the equipment table cannot hold instead of failing in the database', function (): void {
        actingAs(adminWithPermissions('create_equipments'), 'admin');
        $type = EquipmentType::query()->create(['name' => 'Аппараты УЗИ']);

        $this->post(route('equipment.store'), [
            'name' => 'Аппарат УЗИ',
            'equipment_type_id' => $type->id,
            'manufacturer_id' => Manufacturer::factory()->create()->id,
            'staff_id' => Staff::factory()->create()->id,
            'serial_number' => str_repeat('7', 256),
            'date_purchase' => '2025-01-10',
            'calibration_date' => '2025-06-10',
            'repair' => 'Нет',
            'term_date' => '2030-01-10',
            'term_notify_days' => 3_000_000_000,
            'upcoming_service_at' => -5,
            'location' => 'Кабинет 12',
            'price' => '-1',
        ])->assertSessionHasErrors(['serial_number', 'term_notify_days', 'upcoming_service_at', 'price']);

        expect(Equipment::query()->count())->toBe(0);
    });
});
