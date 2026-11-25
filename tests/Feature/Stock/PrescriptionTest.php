<?php

use App\Enums\PatientMedicationStatus;
use App\Exceptions\Stock\InsufficientStockException;
use App\Exceptions\Stock\InvalidStatusTransitionException;
use App\Models\DangerousCombination;
use App\Models\Department;
use App\Models\ExaminationMap;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\WarehouseType;
use App\Services\Clinical\PatientMedicationService;
use Database\Seeders\WarehouseSeeder;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

/** A drug with a batch in the main warehouse at the given unit price. */
function stockPrescribable(string $name, int $unitPrice = 1000): Medication
{
    $medication = Medication::factory()->create(['name' => $name]);
    StockFixtures::mainBatch($medication, 500, StockFixtures::monthsAhead(14), ['retail_price' => $unitPrice]);

    return $medication;
}

/**
 * The prescription form of the patient pages, one entry per line.
 *
 * @param  list<array{0: Medication, 1: int, 2?: list<array{0: Medication, 1: string}>}>  $lines
 * @return array<string, mixed>
 */
function stockPrescriptionForm(Patient $patient, array $lines): array
{
    $form = ['patient_id' => $patient->id];

    foreach ($lines as $index => $line) {
        $form['medications'][] = $line[0]->id;
        $form['dosages'][] = (string) $line[1];
        $form['single_doses'][] = '1 таблетка';
        $form['statuses'][] = 'ordered';
        $form['methods'][] = 'oral';
        $form['admission_rules'][] = 'after_meal';
        $form['date_of_ends'][] = today()->addDays(5)->toDateString();
        $form['admission_periods'][] = '08:00,20:00';
        $form['medication_notes'][] = 'После еды';

        foreach ($line[2] ?? [] as [$compatible, $dosage]) {
            $form['compatible_medications'][$index][] = $compatible->id;
            $form['compatible_dosages'][$index][] = $dosage;
        }
    }

    return $form;
}

/** An inpatient whose department has its own warehouse. */
function stockInpatient(): ExaminationMap
{
    $department = Department::factory()->create(['warehouse_id' => StockFixtures::departmentWarehouse()->id]);

    return ExaminationMap::factory()->create(['department_id' => $department->id]);
}

it('keeps the compatible medications of an inpatient prescription', function (): void {
    // Regression: the compatibles were synced through a relation that skips prescriptions of a stay.
    $map = stockInpatient();
    $drug = stockPrescribable('Амоксициллин');
    $compatible = stockPrescribable('Кларитромицин');

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), stockPrescriptionForm($map->patient, [[$drug, 2, [[$compatible, '250 мг']]]]))
        ->assertRedirect()
        ->assertSessionHas('success');

    $prescription = PatientMedication::query()->sole();

    expect($prescription->examination_map_id)->toBe($map->id)
        ->and($prescription->compatibleMedications->map(fn (Medication $m): array => [$m->id, $m->pivot->dosage])->all())
        ->toBe([[$compatible->id, '250 мг']]);
});

it('leaves the compatibles of earlier prescriptions of the same drug alone', function (): void {
    // Regression: for an outpatient every older row of the drug got the new compatibles.
    $patient = Patient::factory()->create();
    $drug = stockPrescribable('Парацетамол');
    $old = stockPrescribable('Ибупрофен');
    $new = stockPrescribable('Лоратадин');

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), stockPrescriptionForm($patient, [[$drug, 1, [[$old, '200 мг']]]]))->assertSessionHas('success');
    post(route('patient.medication.store'), stockPrescriptionForm($patient, [[$drug, 1, [[$new, '10 мг']]]]))->assertSessionHas('success');

    [$first, $second] = PatientMedication::query()->orderBy('id')->get()->all();

    expect($first->compatibleMedications->pluck('id')->all())->toBe([$old->id])
        ->and($second->compatibleMedications->pluck('id')->all())->toBe([$new->id]);
});

it('prescribes the same drug twice in one form as two prescriptions, always ordered and priced', function (): void {
    $patient = Patient::factory()->create();
    $drug = stockPrescribable('Метамизол натрия', 2500);

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    $form = stockPrescriptionForm($patient, [[$drug, 2], [$drug, 3]]);
    $form['statuses'] = ['completed', 'received_at_station'];

    post(route('patient.medication.store'), $form)->assertSessionHas('success');

    expect(PatientMedication::query()->orderBy('id')->get(['dosage', 'status', 'price'])->toArray())->toBe([
        ['dosage' => '2', 'status' => 'ordered', 'price' => '5000.00'],
        ['dosage' => '3', 'status' => 'ordered', 'price' => '7500.00'],
    ]);
});

it('rejects a form line without the columns every prescription needs', function (): void {
    // The service wrote null into the NOT NULL method / admission_rules columns when a line was short.
    $patient = Patient::factory()->create();
    $drug = stockPrescribable('Омепразол');
    $form = stockPrescriptionForm($patient, [[$drug, 1], [$drug, 1]]);
    array_pop($form['methods']);
    array_pop($form['admission_rules']);
    $form['compatible_medications'][0] = [$drug->id];

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), $form)
        ->assertSessionHasErrors(['methods', 'admission_rules', 'compatible_dosages.0']);

    expect(PatientMedication::query()->exists())->toBeFalse();
});

it('refuses a drug the main warehouse never received instead of prescribing it for free', function (): void {
    $patient = Patient::factory()->create();
    $unknown = Medication::factory()->create(['name' => 'Новый препарат']);

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), stockPrescriptionForm($patient, [[stockPrescribable('Аспирин'), 1], [$unknown, 1]]))
        ->assertSessionHas('error', 'У препарата «Новый препарат» нет цены: он не поступал на главный склад.');

    expect(PatientMedication::query()->exists())->toBeFalse();
});

it('warns the doctor about a dangerous combination with an active prescription', function (): void {
    $patient = Patient::factory()->create();
    PatientMedication::factory()->create(['patient_id' => $patient->id, 'medication_id' => stockPrescribable('Варфарин')->id]);
    DangerousCombination::factory()->create([
        'drug1_ru' => 'Варфарин',
        'drug2_ru' => 'ибупрофен',
        'severity_ru' => 'Высокая',
        'comment_ru' => 'Риск кровотечения.',
    ]);

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), stockPrescriptionForm($patient, [[stockPrescribable('Ибупрофен'), 1]]))
        ->assertSessionHas('success')
        ->assertSessionHas('warning', 'Внимание, опасное сочетание препаратов. Варфарин + ибупрофен (Высокая): Риск кровотечения.');

    expect(PatientMedication::query()->count())->toBe(2);
});

it('does not warn about a combination with a cancelled prescription', function (): void {
    $patient = Patient::factory()->create();
    PatientMedication::factory()->create([
        'patient_id' => $patient->id,
        'medication_id' => stockPrescribable('Варфарин')->id,
        'status' => PatientMedicationStatus::Cancelled->value,
    ]);
    DangerousCombination::factory()->create(['drug1_ru' => 'Варфарин', 'drug2_ru' => 'Ибупрофен']);

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), stockPrescriptionForm($patient, [[stockPrescribable('Ибупрофен'), 1]]))
        ->assertSessionMissing('warning');
});

it('dispenses an inpatient prescription from the department warehouse, earliest expiry first, once', function (): void {
    // Regression: no transition ever decremented any stock.
    $map = stockInpatient();
    $drug = stockPrescribable('Цефтриаксон');
    $warehouse = $map->department->warehouse;
    $sooner = StockFixtures::departmentBatch($warehouse, $drug, 2, StockFixtures::monthsAhead(2));
    $later = StockFixtures::departmentBatch($warehouse, $drug, 10, StockFixtures::monthsAhead(7));
    $prescription = PatientMedication::factory()->create([
        'patient_id' => $map->patient_id,
        'examination_map_id' => $map->id,
        'medication_id' => $drug->id,
        'dosage' => '3',
    ]);
    $service = app(PatientMedicationService::class);

    $service->sendToFloor($prescription);

    expect($prescription->fresh()->status)->toBe(PatientMedicationStatus::SentToFloor)
        ->and($sooner->fresh()->quantity)->toBe(0)
        ->and($later->fresh()->quantity)->toBe(9)
        ->and(fn () => $service->sendToFloor($prescription))
        ->toThrow(InvalidStatusTransitionException::class);

    expect($later->fresh()->quantity)->toBe(9);
});

it('dispenses an outpatient prescription from the main warehouse and refuses a shortage', function (): void {
    $drug = Medication::factory()->create(['name' => 'Глюкоза']);
    $batch = StockFixtures::mainBatch($drug, 4, StockFixtures::monthsAhead(2));
    $prescription = PatientMedication::factory()->create(['medication_id' => $drug->id, 'dosage' => '3']);
    $tooMuch = PatientMedication::factory()->create(['medication_id' => $drug->id, 'dosage' => '2']);
    $service = app(PatientMedicationService::class);

    $service->sendToFloor($prescription);

    expect($batch->fresh()->quantity)->toBe(1)
        ->and(fn () => $service->sendToFloor($tooMuch))
        ->toThrow(InsufficientStockException::class, 'Недостаточно «Глюкоза» (главный склад): нужно 2, в наличии 1.');

    expect($tooMuch->fresh()->status)->toBe(PatientMedicationStatus::Ordered)
        ->and($batch->fresh()->quantity)->toBe(1);
});

it('cancels an ordered prescription and returns a dispensed one into the batches it came from', function (): void {
    $map = stockInpatient();
    $drug = stockPrescribable('Фуросемид');
    $lot = StockFixtures::departmentBatch($map->department->warehouse, $drug, 10, StockFixtures::monthsAhead(2));
    $ordered = PatientMedication::factory()->create(['patient_id' => $map->patient_id, 'examination_map_id' => $map->id, 'medication_id' => $drug->id]);
    $dispensed = PatientMedication::factory()->create(['patient_id' => $map->patient_id, 'examination_map_id' => $map->id, 'medication_id' => $drug->id, 'dosage' => '4']);
    app(PatientMedicationService::class)->sendToFloor($dispensed);

    actingAs(adminWithPermissions('edit_patient_medication'), 'admin');

    postJson(route('api.patient-medications.cancel', $ordered))->assertOk()->assertJson(['status' => 'cancelled']);
    expect($lot->fresh()->quantity)->toBe(6);

    postJson(route('api.patient-medications.cancel', $dispensed))->assertOk()->assertJson(['status' => 'returned']);
    expect($lot->fresh()->quantity)->toBe(10)
        ->and(StockFixtures::movementsOf($lot))->toBe([['dispense', -4], ['return', 4]]);

    postJson(route('api.patient-medications.cancel', $dispensed))
        ->assertUnprocessable()
        ->assertJson(['message' => 'Назначение в статусе «Возвращён» нельзя перевести в статус «Отменён».']);
    expect($lot->fresh()->quantity)->toBe(10);
});

it('extends only a prescription in progress', function (string $status, bool $extended): void {
    // Regression: the condition was always true, so cancelled and returned drugs got extended.
    $prescription = PatientMedication::factory()->create(['status' => $status, 'date_of_end' => '2026-11-20']);

    actingAs(adminWithPermissions('edit_patient_medication'), 'admin');

    $response = postJson(route('api.patient-medications.extend', $prescription));

    $extended ? $response->assertOk() : $response->assertUnprocessable();
    expect($prescription->fresh()->date_of_end)->toBe($extended ? '2026-11-21' : '2026-11-20');
})->with([
    'ordered' => ['ordered', true],
    'received at the station' => ['received_at_station', true],
    'completed' => ['completed', false],
    'returned' => ['returned', false],
    'cancelled' => ['cancelled', false],
]);

it('requires edit_patient_medication to cancel or extend', function (): void {
    $prescription = PatientMedication::factory()->create();

    actingAs(adminWithPermissions('list_patient_medication'), 'admin');

    postJson(route('api.patient-medications.cancel', $prescription))->assertForbidden();
    postJson(route('api.patient-medications.extend', $prescription))->assertForbidden();

    expect($prescription->fresh()->status)->toBe(PatientMedicationStatus::Ordered);
});

it('edits a prescription and its compatible medications', function (): void {
    // The edit form posts compatible_medications[0][]; the old update used the whole array as a key and crashed.
    $prescription = PatientMedication::factory()->create(['medication_id' => stockPrescribable('Эналаприл', 350)->id, 'dosage' => '1']);
    $other = stockPrescribable('Амлодипин', 500);
    $compatible = stockPrescribable('Спиронолактон');

    actingAs(adminWithPermissions('edit_patient_medication'), 'admin');

    get(route('patient.medication.edit', $prescription))->assertOk()->assertSeeText('Заказан');

    put(route('patient.medication.update', $prescription), stockPrescriptionForm($prescription->patient, [[$other, 2, [[$compatible, '25 мг']]]]))
        ->assertRedirect(route('patients.show', $prescription->patient_id))
        ->assertSessionHas('success');

    $prescription->refresh();

    expect($prescription->only(['medication_id', 'dosage', 'price', 'status']))
        ->toBe(['medication_id' => $other->id, 'dosage' => '2', 'price' => '1000.00', 'status' => PatientMedicationStatus::Ordered])
        ->and($prescription->compatibleMedications->pluck('id')->all())->toBe([$compatible->id]);
});

it('keeps the drug and quantity of a prescription already sent to the floor', function (): void {
    $drug = stockPrescribable('Симвастатин');
    $prescription = PatientMedication::factory()->sentToFloor()->create(['medication_id' => $drug->id, 'dosage' => '1']);

    actingAs(adminWithPermissions('edit_patient_medication'), 'admin');

    put(route('patient.medication.update', $prescription), stockPrescriptionForm($prescription->patient, [[$drug, 5]]))
        ->assertSessionHas('error', 'Препарат и количество можно изменить только до отправки назначения на этаж.');

    $form = stockPrescriptionForm($prescription->patient, [[$drug, 1]]);
    $form['admission_periods'] = ['09:00'];

    put(route('patient.medication.update', $prescription), $form)->assertSessionHas('success');

    expect($prescription->fresh()->only(['dosage', 'admission_period', 'status']))
        ->toBe(['dosage' => '1', 'admission_period' => '09:00', 'status' => PatientMedicationStatus::SentToFloor]);
});

it('never dispenses an expired batch, however early it expires', function (): void {
    $map = stockInpatient();
    $drug = stockPrescribable('Ампициллин');
    $warehouse = $map->department->warehouse;
    $expired = StockFixtures::departmentBatch($warehouse, $drug, 20, today()->subDay()->toDateString());
    $usable = StockFixtures::departmentBatch($warehouse, $drug, 5, StockFixtures::monthsAhead(2));
    $prescription = PatientMedication::factory()->create([
        'patient_id' => $map->patient_id,
        'examination_map_id' => $map->id,
        'medication_id' => $drug->id,
        'dosage' => '3',
    ]);
    $tooMuch = PatientMedication::factory()->create([
        'patient_id' => $map->patient_id,
        'examination_map_id' => $map->id,
        'medication_id' => $drug->id,
        'dosage' => '3',
    ]);
    $service = app(PatientMedicationService::class);

    $service->sendToFloor($prescription);

    expect($usable->fresh()->quantity)->toBe(2)
        ->and($expired->fresh()->quantity)->toBe(20)
        ->and(fn () => $service->sendToFloor($tooMuch))
        ->toThrow(InsufficientStockException::class, 'нужно 3, в наличии 2');
});

it('dispenses from the main warehouse when the department warehouse stores no medications', function (): void {
    $department = Department::factory()->create([
        'warehouse_id' => StockFixtures::departmentWarehouse(WarehouseType::HOUSEHOLD_MATERIALS)->id,
    ]);
    $map = ExaminationMap::factory()->create(['department_id' => $department->id]);
    $drug = Medication::factory()->create();
    $batch = StockFixtures::mainBatch($drug, 10, StockFixtures::monthsAhead(4));
    $prescription = PatientMedication::factory()->create([
        'patient_id' => $map->patient_id,
        'examination_map_id' => $map->id,
        'medication_id' => $drug->id,
        'dosage' => '4',
    ]);

    app(PatientMedicationService::class)->sendToFloor($prescription);

    expect($batch->fresh()->quantity)->toBe(6)
        ->and($prescription->fresh()->status)->toBe(PatientMedicationStatus::SentToFloor);
});

it('rejects a form whose parallel lines do not line up instead of failing', function (): void {
    $patient = Patient::factory()->create();
    $form = stockPrescriptionForm($patient, [[stockPrescribable('Мелоксикам'), 1]]);
    $form['dosages'] = [5 => '1'];

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), $form)->assertSessionHasErrors('dosages');

    expect(PatientMedication::query()->exists())->toBeFalse();
});

it('rejects compatible medications of a line the form does not prescribe', function (): void {
    // A removed form row used to leave its compatibles on an index no line had: they were dropped silently.
    $patient = Patient::factory()->create();
    $form = stockPrescriptionForm($patient, [[stockPrescribable('Дексаметазон'), 1]]);
    $form['compatible_medications'] = [1 => [stockPrescribable('Натрия хлорид')->id]];
    $form['compatible_dosages'] = [1 => ['100 мл']];

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), $form)->assertSessionHasErrors('compatible_medications.1');

    expect(PatientMedication::query()->exists())->toBeFalse();
});

it('warns about a dangerous combination whose names were imported in sentence case', function (): void {
    $patient = Patient::factory()->create();
    PatientMedication::factory()->create(['patient_id' => $patient->id, 'medication_id' => stockPrescribable('АЦЕТИЛСАЛИЦИЛОВАЯ КИСЛОТА')->id]);
    DangerousCombination::factory()->create([
        'drug1_ru' => 'Ацетилсалициловая кислота',
        'drug2_ru' => 'Метотрексат',
        'severity_ru' => 'Высокая',
        'comment_ru' => 'Токсичность метотрексата.',
    ]);

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), stockPrescriptionForm($patient, [[stockPrescribable('метотрексат'), 1]]))
        ->assertSessionHas('warning', 'Внимание, опасное сочетание препаратов. Ацетилсалициловая кислота + Метотрексат (Высокая): Токсичность метотрексата.');
});

it('requires create_patient_medication to prescribe and edit_patient_medication to change a prescription', function (): void {
    $drug = stockPrescribable('Пантопразол');
    $prescription = PatientMedication::factory()->create(['medication_id' => $drug->id, 'dosage' => '1']);
    $form = stockPrescriptionForm($prescription->patient, [[$drug, 7]]);

    actingAs(adminWithPermissions('view_patients'), 'admin');

    post(route('patient.medication.store'), $form)->assertForbidden();
    get(route('patient.medication.edit', $prescription))->assertForbidden();
    put(route('patient.medication.update', $prescription), $form)->assertForbidden();

    expect(PatientMedication::query()->count())->toBe(1)
        ->and($prescription->fresh()->dosage)->toBe('1');
});

it('does not open the edit form of a prescription that is over', function (): void {
    $prescription = PatientMedication::factory()->create(['status' => PatientMedicationStatus::Returned->value]);

    actingAs(adminWithPermissions('edit_patient_medication'), 'admin');

    get(route('patient.medication.edit', $prescription))
        ->assertRedirect(route('patients.show', $prescription->patient_id))
        ->assertSessionHas('error', 'Назначение в статусе «Возвращён» изменить нельзя.');
});

it('returns a dispensed prescription whole or not at all when one of its batches was written off', function (): void {
    $drug = Medication::factory()->create();
    $sooner = StockFixtures::mainBatch($drug, 2, StockFixtures::monthsAhead(2));
    $later = StockFixtures::mainBatch($drug, 10, StockFixtures::monthsAhead(8));
    $prescription = PatientMedication::factory()->create(['medication_id' => $drug->id, 'dosage' => '5']);
    app(PatientMedicationService::class)->sendToFloor($prescription);

    actingAs(adminWithPermissions('edit_patient_medication', 'edit_main_warehouse'), 'admin');

    // The units came from both batches; the second one is gone when the drug comes back.
    delete(route('main.warehouse.receipts.goods.discard', ['type' => 'medications', 'id' => $later->id]))->assertSessionHas('success');

    postJson(route('api.patient-medications.cancel', $prescription))
        ->assertUnprocessable()
        ->assertJson(['message' => 'Партия, из которой выдан препарат, уже списана: вернуть его на склад нельзя.']);

    expect($sooner->fresh()->quantity)->toBe(0)
        ->and($prescription->fresh()->status)->toBe(PatientMedicationStatus::SentToFloor);
});

it('rejects a note longer than its column instead of failing', function (): void {
    $patient = Patient::factory()->create();
    $form = stockPrescriptionForm($patient, [[stockPrescribable('Цетиризин'), 1]]);
    $form['medication_notes'] = [str_repeat('а', 300)];

    actingAs(adminWithPermissions('create_patient_medication'), 'admin');

    post(route('patient.medication.store'), $form)->assertSessionHasErrors('medication_notes.0');

    expect(PatientMedication::query()->exists())->toBeFalse();
});
