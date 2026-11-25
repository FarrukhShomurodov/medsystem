<?php

use App\Exports\PatientRecordsExport;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Reception;
use App\Models\Service;
use App\Queries\Billing\BillableItem;
use App\Services\Billing\Amount;
use Maatwebsite\Excel\Facades\Excel;

use function Pest\Laravel\actingAs;

/*
| The Excel export of the registrar card: exactly the basket the registrar confirms.
*/

beforeEach(function (): void {
    Excel::fake();

    $this->patient = Patient::factory()->create();
    $this->export = fn () => actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('registrar.export', $this->patient));
});

it('exports the whole basket, procedures included, with their prices in the total', function (): void {
    $service = PatientService::factory()->for($this->patient)
        ->for(Service::factory()->state(['price' => '100000.00', 'currency' => null]))
        ->create(['status' => 'assigned']);
    $reception = Reception::factory()->for($this->patient)
        ->for(Service::factory()->appointment()->state(['price' => '30000.00', 'currency' => 'UZS']))
        ->create(['status' => 'new']);
    $procedure = PatientProcedure::factory()->for($this->patient)
        ->for(Procedure::factory()->state(['name' => 'Массаж спины', 'price' => '50000.00']))
        ->create(['status' => 'assigned']);
    $medication = PatientMedication::factory()->for($this->patient)->create(['status' => 'ordered', 'price' => '2500.50']);

    ($this->export)()->assertOk();

    Excel::assertDownloaded('patient_records.xlsx', function (PatientRecordsExport $export) use ($service, $reception, $procedure, $medication): true {
        expect($export->items->map->key()->all())->toBe([
            "service:{$service->id}",
            "reception:{$reception->id}",
            "procedure:{$procedure->id}",
            "medication:{$medication->id}",
        ])
            // Orders of different kinds can share an id, so the item is found by its key.
            ->and($export->items->first(fn (BillableItem $item): bool => $item->key() === "procedure:{$procedure->id}")->name)->toBe('Массаж спины')
            ->and(array_map(fn (Amount $sum): string => $sum->toDecimal(), $export->totals->all()))->toBe(['UZS' => '182500.50']);

        return true;
    });
});

it('leaves out orders the registrar no longer confirms', function (): void {
    $closedMap = ExaminationMap::factory()->discharged()->for($this->patient)->create();
    PatientService::factory()->confirmed()->for($this->patient)->create();
    PatientProcedure::factory()->for($this->patient)->create(['status' => 'assigned', 'examination_map_id' => $closedMap->id]);
    PatientMedication::factory()->sentToFloor()->for($this->patient)->create();
    PatientService::factory()->create(['status' => 'assigned']);

    ($this->export)()->assertOk();

    Excel::assertDownloaded('patient_records.xlsx', function (PatientRecordsExport $export): true {
        expect($export->items)->toBeEmpty()
            ->and(array_map(fn (Amount $sum): string => $sum->toDecimal(), $export->totals->all()))->toBe(['UZS' => '0.00']);

        return true;
    });
});

it('keeps accounts that cannot view patients away from the export', function (): void {
    actingAs(adminWithPermissions('view_cashier'), 'admin')
        ->get(route('registrar.export', $this->patient))
        ->assertForbidden();
});
