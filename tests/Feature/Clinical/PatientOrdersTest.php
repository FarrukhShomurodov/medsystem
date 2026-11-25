<?php

use App\Enums\ServicePlace;
use App\Enums\ServiceStatus;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\ServiceType;
use Database\Seeders\ServiceTypeSeeder;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| Services and procedures ordered for a patient: the patient card, the patient services pages and
| the status select of the patient card history.
*/

function changeServiceStatus(PatientService $service, string $status): TestResponse
{
    return test()->patchJson(route('api.patient-services.update-status', $service), ['status' => $status]);
}

/**
 * @return array<string, mixed>
 */
function serviceOrder(PatientService|Patient $for, array $overrides = []): array
{
    $service = $for instanceof PatientService ? $for : null;

    return [
        'service_id' => $service->service_id ?? Service::factory()->create()->id,
        'patient_id' => $service->patient_id ?? $for->id,
        'service_date' => '2026-11-20',
        'service_time' => '10:30',
        'place' => 'cabinet',
        ...$overrides,
    ];
}

describe('service status on the patient card', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('edit_patient_services'), 'admin');
    });

    it('confirms an assigned service', function (): void {
        $service = PatientService::factory()->create();

        changeServiceStatus($service, 'confirmed')->assertOk()->assertJson(['status' => 'confirmed']);

        expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
    });

    it('leaves the payment to the cashier', function (): void {
        $service = PatientService::factory()->confirmed()->create();

        // The old endpoint accepted every status of PatientService::STATUS, "paid" included.
        changeServiceStatus($service, 'paid')->assertUnprocessable()->assertJsonValidationErrors('status');

        expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
    });

    it('does not put a paid service back into the registrar basket', function (): void {
        $service = PatientService::factory()->create(['status' => 'paid']);

        changeServiceStatus($service, 'assigned')
            ->assertUnprocessable()
            ->assertJson(['message' => 'Нельзя сменить статус услуги «Оплачена» на «Назначена».']);

        expect($service->fresh()->status)->toBe(ServiceStatus::Paid);
    });

    it('does not reopen a completed or cancelled service', function (string $final, string $next): void {
        $service = PatientService::factory()->create(['status' => $final]);

        changeServiceStatus($service, $next)->assertUnprocessable();

        expect($service->fresh()->status->value)->toBe($final);
    })->with([
        ['completed', 'in_process'],
        ['cancelled', 'assigned'],
        ['cancelled', 'confirmed'],
    ]);

    it('carries out a paid service', function (): void {
        $service = PatientService::factory()->create(['status' => 'paid']);

        changeServiceStatus($service, 'in_process')->assertOk();
        changeServiceStatus($service, 'completed')->assertOk();

        expect($service->fresh()->status)->toBe(ServiceStatus::Completed);
    });

    it('answers a repeated request for the current status without changing anything', function (): void {
        $service = PatientService::factory()->confirmed()->create();
        $updatedAt = $service->updated_at;

        $this->travel(5)->minutes();
        changeServiceStatus($service, 'confirmed')->assertOk();

        expect($service->fresh()->updated_at->equalTo($updatedAt))->toBeTrue();
    });

    it('refuses an unknown status', function (): void {
        changeServiceStatus(PatientService::factory()->create(), 'archived')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    });

    it('needs the permission to edit patient services, not just to see the patient', function (): void {
        actingAs(adminWithPermissions('view_patients'), 'admin');
        $service = PatientService::factory()->create();

        changeServiceStatus($service, 'confirmed')->assertForbidden();

        expect($service->fresh()->status)->toBe(ServiceStatus::Assigned);
    });
});

describe('patient service pages', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_patient_services', 'create_patient_services', 'edit_patient_services'), 'admin');
    });

    it('orders a service as assigned, whatever status the form sends', function (): void {
        $patient = Patient::factory()->create();

        $this->post(route('patient.services.store'), serviceOrder($patient, ['status' => 'paid']))
            ->assertRedirect(route('patient.services.index'))
            ->assertSessionHas('success', 'Услуга назначена!');

        expect(PatientService::query()->sole()->only(['status', 'place', 'service_time']))
            ->toBe(['status' => ServiceStatus::Assigned, 'place' => ServicePlace::Cabinet, 'service_time' => '10:30:00']);
    });

    it('attaches a new order to the active examination map of the patient', function (): void {
        $map = ExaminationMap::factory()->create();

        $this->post(route('patient.services.store'), serviceOrder($map->patient))->assertSessionHasNoErrors();

        expect(PatientService::query()->sole()->examination_map_id)->toBe($map->id);
    });

    it('refuses the examination map of another patient', function (): void {
        $patient = Patient::factory()->create();
        $foreignMap = ExaminationMap::factory()->discharged()->create();

        $this->post(route('patient.services.store'), serviceOrder($patient, ['examination_map_id' => $foreignMap->id]))
            ->assertSessionHasErrors('examination_map_id');
    });

    it('keeps the status of a paid service when its details are edited', function (): void {
        $service = PatientService::factory()->create(['status' => 'paid']);

        // The edit form used to post a hidden status "assigned": the paid service went back to the
        // registrar basket and was billed again.
        $this->put(route('patient.services.update', $service), serviceOrder($service, ['status' => 'assigned', 'result' => 'Норма']))
            ->assertRedirect(route('patient.services.index'))
            ->assertSessionHas('success', 'Назначенная услуга успешно обновлена!');

        expect($service->fresh()->only(['status', 'result']))->toBe(['status' => ServiceStatus::Paid, 'result' => 'Норма']);
    });

    it('keeps the service and the patient of a paid order', function (array $change): void {
        $service = PatientService::factory()->create(['status' => 'paid']);
        $original = $service->only(['service_id', 'patient_id']);

        // The status was kept, but the paid order could be moved to another service or patient,
        // so the payment no longer matched what was done and for whom.
        $this->from(route('patient.services.edit', $service))
            ->put(route('patient.services.update', $service), serviceOrder($service, $change))
            ->assertRedirect(route('patient.services.edit', $service))
            ->assertSessionHas('error', 'Услуга уже оплачена: сменить услугу или пациента нельзя.');

        expect($service->fresh()->only(['service_id', 'patient_id']))->toBe($original);
    })->with([
        'another service' => [fn (): array => ['service_id' => Service::factory()->create()->id]],
        'another patient' => [fn (): array => ['patient_id' => Patient::factory()->create()->id]],
    ]);

    it('orders only services on the price list, and keeps an order whose service was taken off it editable', function (): void {
        $patient = Patient::factory()->create();
        $withdrawn = Service::factory()->create(['is_active' => false]);

        $this->post(route('patient.services.store'), serviceOrder($patient, ['service_id' => $withdrawn->id]))
            ->assertSessionHasErrors('service_id');

        expect(PatientService::query()->exists())->toBeFalse();

        $order = PatientService::factory()->for($patient)->create();
        $order->service->update(['is_active' => false]);

        $this->put(route('patient.services.update', $order), serviceOrder($order, ['result' => 'Норма']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.services.index'));

        $this->put(route('patient.services.update', $order), serviceOrder($order, ['service_id' => $withdrawn->id]))
            ->assertSessionHasErrors('service_id');

        expect($order->fresh()->only(['service_id', 'result']))->toBe(['service_id' => $order->service_id, 'result' => 'Норма']);
    });

    it('moves an unpaid order to another service', function (): void {
        $service = PatientService::factory()->confirmed()->create();
        $other = Service::factory()->create();

        $this->put(route('patient.services.update', $service), serviceOrder($service, ['service_id' => $other->id]))
            ->assertRedirect(route('patient.services.index'));

        expect($service->fresh()->service_id)->toBe($other->id);
    });

    it('does not delete the record of a payment', function (string $status): void {
        actingAs(adminWithPermissions('list_patient_services', 'delete_patient_services'), 'admin');
        $service = PatientService::factory()->create(['status' => $status]);

        $this->from(route('patient.services.index'))
            ->delete(route('patient.services.destroy', $service))
            ->assertRedirect(route('patient.services.index'))
            ->assertSessionHas('error', 'Оплаченную услугу нельзя удалить.');

        expect($service->fresh())->not->toBeNull();
    })->with(['paid', 'in_process', 'completed']);

    it('deletes an order that is not paid', function (string $status): void {
        actingAs(adminWithPermissions('list_patient_services', 'delete_patient_services'), 'admin');
        $service = PatientService::factory()->create(['status' => $status]);

        $this->delete(route('patient.services.destroy', $service))
            ->assertRedirect(route('patient.services.index'))
            ->assertSessionHas('success', 'Назначенная услуга успешно удалена!');

        expect($service->fresh())->toBeNull();
    })->with(['assigned', 'confirmed', 'cancelled']);

    it('renders the list and the forms', function (): void {
        $service = PatientService::factory()->create();
        PatientService::factory()->create(['status' => 'paid']);

        // Paid services had no badge style and were listed as "Неизвестно".
        $this->get(route('patient.services.index'))
            ->assertOk()
            ->assertSee($service->service->name)
            ->assertSee('Оплачена')
            ->assertDontSee('Неизвестно');
        $this->get(route('patient.services.create'))->assertOk();
        $this->get(route('patient.services.edit', $service))->assertOk();
    });

    it('offers to delete only the orders that are not paid', function (): void {
        actingAs(adminWithPermissions('list_patient_services', 'delete_patient_services'), 'admin');
        $unpaid = PatientService::factory()->confirmed()->create();
        $paid = PatientService::factory()->create(['status' => 'paid']);

        $this->get(route('patient.services.index'))
            ->assertOk()
            ->assertSee(route('patient.services.destroy', $unpaid))
            ->assertDontSee(route('patient.services.destroy', $paid))
            ->assertDontSee(route('patient.services.edit', $unpaid));
    });

    describe('the patient field of the order form', function (): void {
        // The field listed the whole register: about 6 MB of options and 115 MB of models per
        // page at 50,000 patients, past PHP's default memory limit. It searches through
        // /api/patients now and prints only the patient already picked.
        it('lists no patient on the create form and searches the register instead', function (): void {
            $patients = Patient::factory()->count(3)->create();

            $page = $this->get(route('patient.services.create'))
                ->assertOk()
                ->assertSeeHtml('data-patients-url="'.route('api.patients.index').'"');

            foreach ($patients as $patient) {
                $page->assertDontSee($patient->full_name);
            }
        });

        it('prints only the patient of the order on the edit form', function (): void {
            $service = PatientService::factory()
                ->for(Patient::factory()->state(['last_name' => 'Каримова', 'first_name' => 'Дильноза', 'middle_name' => null, 'birth_date' => '1990-04-12']))
                ->create();
            $other = Patient::factory()->create();

            $this->get(route('patient.services.edit', $service))
                ->assertOk()
                ->assertSeeHtml("<option value=\"{$service->patient_id}\" selected>Каримова Дильноза (12.04.1990)</option>")
                ->assertDontSee($other->full_name);
        });

        it('keeps the patient picked before a refused submission', function (): void {
            $service = PatientService::factory()->create();
            $picked = Patient::factory()->create();

            $this->withSession(['_old_input' => ['patient_id' => (string) $picked->id]])
                ->get(route('patient.services.edit', $service))
                ->assertOk()
                ->assertSeeHtml("<option value=\"{$picked->id}\" selected>")
                ->assertDontSee($service->patient->full_name);
        });

        it('ignores a patient id of a refused submission that is not one', function (mixed $old): void {
            // Patient::find('abc') on a bigint key is a QueryException, a 500 for the form.
            $this->withSession(['_old_input' => ['patient_id' => $old]])
                ->get(route('patient.services.create'))
                ->assertOk()
                ->assertViewHas('selectedPatient', null);
        })->with(['not a number' => 'abc', 'out of range' => '99999999999999999999', 'an array' => [['1']], 'no such patient' => '999999']);
    });
});

describe('service picker of the patient card', function (): void {
    beforeEach(function (): void {
        seed(ServiceTypeSeeder::class);
        actingAs(adminWithPermissions('create_patient_services'), 'admin');
    });

    it('lists the active services of the type', function (): void {
        $ecg = Service::factory()->create(['service_type_id' => 2, 'name' => 'ЭКГ']);
        Service::factory()->create(['service_type_id' => 2, 'is_active' => false]);
        Service::factory()->create(['service_type_id' => 3]);

        $this->getJson(route('api.services.by-type', 2))->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $ecg->id);
    });

    it('lists only the instrumental studies booked by appointment', function (): void {
        $booked = Service::factory()->appointment()->create(['service_type_id' => ServiceType::INSTRUMENTAL_STUDIES_ID]);
        Service::factory()->create(['service_type_id' => ServiceType::INSTRUMENTAL_STUDIES_ID]);

        $this->getJson(route('api.services.by-type', ServiceType::INSTRUMENTAL_STUDIES_ID))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $booked->id);
    });
});

describe('procedures ordered from the patient card', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('create_patient_procedure'), 'admin');
    });

    it('orders the same procedure on two dates as two procedures', function (): void {
        $patient = Patient::factory()->create();
        $dressing = Procedure::factory()->create(['name' => 'Перевязка']);

        // The old service keyed the orders by procedure id, so the second date replaced the first.
        $this->from(route('patients.show', $patient))
            ->post(route('patient.procedures.store'), [
                'patient_id' => $patient->id,
                'procedures' => [$dressing->id, $dressing->id],
                'procedure_dates' => ['2026-11-20', '2026-11-22'],
            ])
            ->assertRedirect(route('patients.show', $patient))
            ->assertSessionHas('success', 'Процедуры назначены!');

        expect(PatientProcedure::query()->orderBy('procedure_date')->get()->map->only(['procedure_id', 'procedure_date', 'status'])->all())
            ->toBe([
                ['procedure_id' => $dressing->id, 'procedure_date' => '2026-11-20', 'status' => ServiceStatus::Assigned],
                ['procedure_id' => $dressing->id, 'procedure_date' => '2026-11-22', 'status' => ServiceStatus::Assigned],
            ]);
    });

    it('takes procedures from the catalogue only', function (): void {
        $patient = Patient::factory()->create();
        Procedure::factory()->create(['name' => 'Капельница']);

        // A typed-in name went to firstOrCreate(['name']), which fails on the NOT NULL price and
        // merged two catalogue procedures of the same name into one.
        $this->post(route('patient.procedures.store'), [
            'patient_id' => $patient->id,
            'procedures' => ['Капельница'],
            'procedure_dates' => ['2026-11-20'],
        ])->assertSessionHasErrors('procedures.0');

        expect(Procedure::query()->count())->toBe(1)
            ->and(PatientProcedure::query()->count())->toBe(0);
    });

    it('needs a date for every procedure', function (): void {
        $patient = Patient::factory()->create();
        $procedures = Procedure::factory()->count(2)->create();

        $this->post(route('patient.procedures.store'), [
            'patient_id' => $patient->id,
            'procedures' => $procedures->modelKeys(),
            'procedure_dates' => ['2026-11-20'],
        ])->assertSessionHasErrors('procedure_dates');
    });

    it('attaches the procedures to the active examination map', function (): void {
        $map = ExaminationMap::factory()->create();

        $this->post(route('patient.procedures.store'), [
            'patient_id' => $map->patient_id,
            'procedures' => [Procedure::factory()->create()->id],
            'procedure_dates' => ['2026-11-20'],
        ])->assertSessionHasNoErrors();

        expect(PatientProcedure::query()->sole()->examination_map_id)->toBe($map->id);
    });

    it('is closed to accounts that cannot order procedures', function (): void {
        actingAs(adminWithPermissions('view_patients'), 'admin');

        $this->post(route('patient.procedures.store'), [])->assertForbidden();
    });
});
