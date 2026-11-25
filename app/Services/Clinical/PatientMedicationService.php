<?php

namespace App\Services\Clinical;

use App\Enums\PatientMedicationStatus;
use App\Enums\StockMovementReason;
use App\Exceptions\Stock\InvalidStatusTransitionException;
use App\Exceptions\Stock\StockException;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Services\Stock\StockLedger;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Prescriptions (patient_medications) and their lifecycle (see PatientMedicationStatus).
 *
 * The drug leaves stock when the prescription is sent to the floor: that is when it physically
 * leaves the warehouse for the ward, and the cashier's payment that follows (received at the
 * nurse station) does not move anything. An inpatient's drug is dispensed from the medication
 * warehouse of the department of the stay; an outpatient prescription, or a department without a
 * medication warehouse of its own, is served by the main warehouse. Expired batches are never
 * dispensed. A dispensed prescription that is cancelled is returned:
 * its units go back into the batches they came from (stock rules: see StockLedger).
 */
class PatientMedicationService
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * One prescription per line, always ordered, priced at prescribing time. An inpatient's lines
     * belong to the active stay.
     *
     * @param  array<string, mixed>  $validated
     * @return Collection<int, PatientMedication>
     */
    public function store(array $validated): Collection
    {
        $patient = Patient::query()->findOrFail((int) $validated['patient_id']);
        $mapId = $patient->activeExaminationMap()->value('id');
        $medications = Medication::query()->findMany($validated['medications'])->keyBy('id');

        return DB::transaction(function () use ($validated, $patient, $mapId, $medications): Collection {
            $prescriptions = new Collection;

            foreach ($validated['medications'] as $line => $medicationId) {
                $medication = $medications->get((int) $medicationId)
                    ?? throw (new ModelNotFoundException)->setModel(Medication::class, [$medicationId]);
                $units = (int) $validated['dosages'][$line];

                $prescription = PatientMedication::query()->create([
                    'patient_id' => $patient->id,
                    'examination_map_id' => $mapId,
                    'medication_id' => $medication->id,
                    'status' => PatientMedicationStatus::Ordered,
                    'price' => $this->price($medication, $units),
                    ...$this->details($validated, $line),
                ]);

                $this->syncCompatibles($prescription, $validated, $line);
                $prescriptions->push($prescription);
            }

            return $prescriptions;
        });
    }

    /**
     * Changes a prescription that is still in progress. The drug and its quantity (and so the
     * price) can change only while it is ordered: after that the units have left the warehouse.
     *
     * @param  array<string, mixed>  $validated  the form of one prescription (line 0)
     */
    public function update(PatientMedication $prescription, array $validated): PatientMedication
    {
        return DB::transaction(function () use ($prescription, $validated): PatientMedication {
            $prescription = $this->lock($prescription);
            $status = $prescription->status;

            if ($status->isFinal()) {
                throw new StockException("Назначение в статусе «{$status->label()}» изменить нельзя.");
            }

            $medicationId = (int) $validated['medications'][0];
            $units = (int) $validated['dosages'][0];
            $changesStock = $medicationId !== $prescription->medication_id || $units !== $prescription->units();

            if ($changesStock && $status !== PatientMedicationStatus::Ordered) {
                throw new StockException('Препарат и количество можно изменить только до отправки назначения на этаж.');
            }

            $prescription->fill($this->details($validated, 0));

            if ($changesStock) {
                $prescription->fill([
                    'medication_id' => $medicationId,
                    'price' => $this->price(Medication::query()->findOrFail($medicationId), $units),
                ]);
            }

            $prescription->save();
            $this->syncCompatibles($prescription, $validated, 0);

            return $prescription;
        });
    }

    /**
     * Sends an ordered prescription to the ward: its units leave the dispensing warehouse, earliest
     * expiry first. Called for each prescription the registrar confirms.
     *
     * @throws StockException
     */
    public function sendToFloor(PatientMedication $prescription): PatientMedication
    {
        return DB::transaction(function () use ($prescription): PatientMedication {
            $prescription = $this->lock($prescription);
            $this->guardTransition($prescription, PatientMedicationStatus::SentToFloor);

            $this->dispense($prescription);
            $prescription->update(['status' => PatientMedicationStatus::SentToFloor]);

            return $prescription;
        });
    }

    /**
     * Cancels an ordered prescription. One already sent to the ward is returned instead, and its
     * units go back to the batches they were taken from.
     *
     * @throws StockException
     */
    public function cancel(PatientMedication $prescription): PatientMedication
    {
        return DB::transaction(function () use ($prescription): PatientMedication {
            $prescription = $this->lock($prescription);
            $status = $prescription->status;

            if ($status->canTransitionTo(PatientMedicationStatus::Cancelled)) {
                $prescription->update(['status' => PatientMedicationStatus::Cancelled]);

                return $prescription;
            }

            if (! $status->canTransitionTo(PatientMedicationStatus::Returned)) {
                throw new InvalidStatusTransitionException('Назначение', $status->label(), PatientMedicationStatus::Cancelled->label());
            }

            $this->ledger->restore($prescription, StockMovementReason::Dispense, StockMovementReason::Return);
            $prescription->update(['status' => PatientMedicationStatus::Returned]);

            return $prescription;
        });
    }

    /**
     * Extends a prescription in progress by one day.
     *
     * @throws StockException
     */
    public function extend(PatientMedication $prescription): PatientMedication
    {
        return DB::transaction(function () use ($prescription): PatientMedication {
            $prescription = $this->lock($prescription);
            $status = $prescription->status;

            if ($status->isFinal()) {
                throw new StockException("Назначение в статусе «{$status->label()}» продлить нельзя.");
            }

            $prescription->update(['date_of_end' => Carbon::parse($prescription->date_of_end)->addDay()->toDateString()]);

            return $prescription;
        });
    }

    /**
     * The columns of one form line that every prescription row needs (all NOT NULL but the notes).
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function details(array $validated, int $line): array
    {
        return [
            'dosage' => (string) (int) $validated['dosages'][$line],
            'single_dose' => $validated['single_doses'][$line],
            'method' => $validated['methods'][$line],
            'admission_rules' => $validated['admission_rules'][$line],
            'date_of_end' => $validated['date_of_ends'][$line],
            'admission_period' => $validated['admission_periods'][$line],
            'medication_notes' => $validated['medication_notes'][$line] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncCompatibles(PatientMedication $prescription, array $validated, int $line): void
    {
        $dosages = $validated['compatible_dosages'][$line] ?? [];
        $compatibles = [];

        foreach ($validated['compatible_medications'][$line] ?? [] as $index => $medicationId) {
            $compatibles[(int) $medicationId] = ['dosage' => $dosages[$index]];
        }

        $prescription->compatibleMedications()->sync($compatibles);
    }

    /**
     * The unit price is the one of the latest batch received by the main warehouse.
     *
     * @throws StockException when the drug never came into the warehouse
     */
    private function price(Medication $medication, int $units): string
    {
        $batch = MainWarehouseMedication::query()
            ->where('main_warehouse_id', MainWarehouse::MAIN)
            ->where('medication_id', $medication->id)
            ->latest('date_of_receipt')
            ->latest('id')
            ->first();

        $unitPrice = $batch->retail_price ?? $batch->incoming_price_per_unit
            ?? throw new StockException("У препарата «{$medication->name}» нет цены: он не поступал на главный склад.");

        return (string) BigDecimal::of((string) $unitPrice)->multipliedBy($units)->toScale(2, RoundingMode::HalfUp);
    }

    /**
     * Takes the units of the prescription, batches in date earliest expiry first, from the
     * medication warehouse of the department of the stay, or from the main warehouse for an
     * outpatient or a department without a medication warehouse of its own.
     *
     * @throws StockException
     */
    private function dispense(PatientMedication $prescription): void
    {
        $prescription->load('examinationMap.department.warehouse');
        $warehouse = $prescription->examinationMap?->department?->warehouse;
        $name = $prescription->medication()->firstOrFail()->name;

        if ($warehouse instanceof Warehouse && $warehouse->stockItemClass() === Medication::class) {
            $lots = WarehouseAccounting::query()
                ->whereBelongsTo($warehouse)
                ->where('medication_id', $prescription->medication_id)
                ->unexpired()
                ->orderBy('expiration_date')
                ->orderBy('id');

            $this->ledger->take($lots, $prescription->units(), StockMovementReason::Dispense, $prescription, $name, "склад «{$warehouse->name}»");

            return;
        }

        $batches = MainWarehouseMedication::query()
            ->where('main_warehouse_id', MainWarehouse::MAIN)
            ->where('medication_id', $prescription->medication_id)
            ->unexpired()
            ->orderBy('expiration_date')
            ->orderBy('id');

        $this->ledger->take($batches, $prescription->units(), StockMovementReason::Dispense, $prescription, $name, 'главный склад');
    }

    private function lock(PatientMedication $prescription): PatientMedication
    {
        return PatientMedication::query()->whereKey($prescription->getKey())->lockForUpdate()->firstOrFail();
    }

    /** @throws InvalidStatusTransitionException */
    private function guardTransition(PatientMedication $prescription, PatientMedicationStatus $to): void
    {
        $from = $prescription->status;

        if (! $from->canTransitionTo($to)) {
            throw new InvalidStatusTransitionException('Назначение', $from->label(), $to->label());
        }
    }
}
