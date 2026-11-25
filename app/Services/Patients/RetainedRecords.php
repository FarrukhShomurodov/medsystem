<?php

namespace App\Services\Patients;

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Exceptions\Patients\DeletionRefusedException;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Services\Billing\BillableType;

/**
 * The records a patient or a stay may not take with it. Deleting either cascades in the database
 * to every order of it, so the deletion is refused while one of them was paid at the cash desk or
 * had its drug taken from the warehouse (the stock ledger points to it), or while the patient has
 * a closed stay, which is a finished medical record.
 *
 * Call the checks inside the deleting transaction. They lock the orders kind by kind in the order
 * the cashier locks them (BillableType), so a payment taken meanwhile either waits for the
 * deletion and then finds nothing to pay, or commits first and is seen by the check.
 */
final class RetainedRecords
{
    /**
     * @throws DeletionRefusedException
     */
    public function ensurePatientDeletable(Patient $patient): void
    {
        $kinds = $this->retainedOrders('patient_id', $patient->id);

        // Locked like the orders: a discharge committed meanwhile is read from the locked row.
        $stayIsActive = ExaminationMap::query()
            ->whereBelongsTo($patient)
            ->orderBy('id')
            ->lockForUpdate()
            ->pluck('is_active')
            ->all();

        if (in_array(false, $stayIsActive, true)) {
            $kinds[] = 'завершённые госпитализации';
        }

        if ($kinds !== []) {
            throw DeletionRefusedException::patient($kinds);
        }
    }

    /**
     * @throws DeletionRefusedException
     */
    public function ensureStayDeletable(ExaminationMap $map): void
    {
        $kinds = $this->retainedOrders('examination_map_id', $map->id);

        if ($kinds !== []) {
            throw DeletionRefusedException::stay($kinds);
        }
    }

    /**
     * What the orders of the patient or the stay hold that must be kept, one entry per kind.
     *
     * @param  'patient_id'|'examination_map_id'  $owner
     * @return list<string>
     */
    private function retainedOrders(string $owner, int $ownerId): array
    {
        $kinds = [];

        foreach (BillableType::cases() as $type) {
            // Every order of the owner is locked, not only the retained ones: an order the cashier
            // is paying right now is still "confirmed" in this transaction's snapshot.
            $orders = $type->query()
                ->where("{$type->table()}.{$owner}", $ownerId)
                ->orderBy('id')
                ->lockForUpdate()
                ->toBase()
                ->get(['status', 'paid_at']);

            $retained = $this->retainedStatuses($type);

            // A recorded payment is kept whatever the order's status became since.
            if ($orders->contains(fn (object $order): bool => $order->paid_at !== null || in_array($order->status, $retained, true))) {
                $kinds[] = $this->describe($type);
            }
        }

        return $kinds;
    }

    /**
     * The statuses in which an order of the kind is a record of a payment or of a stock movement.
     *
     * @return list<string>
     */
    private function retainedStatuses(BillableType $type): array
    {
        $statuses = match ($type) {
            // The statuses billing counts as paid (the cashier's paid list uses the same ones).
            BillableType::Service, BillableType::Procedure, BillableType::Reception => $type->paidStatuses(),
            // From "sent to the floor" on, the drug has left the warehouse; a returned one went
            // back into the batches it came from, and the ledger keeps both movements.
            BillableType::Medication => [
                PatientMedicationStatus::SentToFloor,
                PatientMedicationStatus::ReceivedAtStation,
                PatientMedicationStatus::Completed,
                PatientMedicationStatus::Returned,
            ],
        };

        return array_map(fn (ServiceStatus|ReceptionStatus|PatientMedicationStatus $status): string => $status->value, $statuses);
    }

    private function describe(BillableType $type): string
    {
        return match ($type) {
            BillableType::Service => 'оплаченные услуги',
            BillableType::Reception => 'оплаченные приёмы',
            BillableType::Procedure => 'оплаченные процедуры',
            BillableType::Medication => 'препараты, выданные со склада',
        };
    }
}
