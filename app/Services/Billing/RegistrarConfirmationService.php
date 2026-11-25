<?php

namespace App\Services\Billing;

use App\Exceptions\Stock\StockException;
use App\Models\Patient;
use App\Queries\Billing\RegistrarBasketQuery;
use App\Services\Clinical\PatientMedicationService;
use Illuminate\Support\Facades\DB;

/**
 * The registrar's confirmation of a patient's basket: every order of it moves from
 * "ordered" to "to be paid" (prescriptions: sent to the floor) in one transaction.
 */
final readonly class RegistrarConfirmationService
{
    /** The orders that only change their status; prescriptions also take their drug from stock. */
    private const array STATUS_ONLY = [BillableType::Service, BillableType::Reception, BillableType::Procedure];

    /** Two confirmations that lock the same stock rows can still deadlock; PostgreSQL aborts one and it is run again. */
    private const int ATTEMPTS = 3;

    public function __construct(
        private RegistrarBasketQuery $basket,
        private PatientMedicationService $prescriptions,
    ) {}

    /**
     * Each UPDATE only matches orders still in their ordered status, and PostgreSQL re-checks
     * that condition on rows a concurrent request has just changed, so a repeated confirmation
     * finds nothing left to move instead of moving anything twice.
     *
     * @return int the number of orders confirmed; 0 when the basket was already empty
     *
     * @throws StockException when a prescribed drug is short: then nothing is confirmed
     */
    public function confirm(Patient $patient): int
    {
        return DB::transaction(function () use ($patient): int {
            $confirmed = 0;

            foreach (self::STATUS_ONLY as $type) {
                $confirmed += $this->basket->pending($patient, $type)->update(['status' => $type->payableStatus()]);
            }

            return $confirmed + $this->sendPrescriptionsToFloor($patient);
        }, self::ATTEMPTS);
    }

    /**
     * Sending a prescription to the floor is when its drug leaves the warehouse, so it goes through
     * the prescription lifecycle rather than a bare status update. The rows are locked first: a
     * concurrent confirmation waits here and then finds them no longer ordered. Taking the drugs in
     * medication order makes confirmations of different patients lock shared stock rows in the same order.
     */
    private function sendPrescriptionsToFloor(Patient $patient): int
    {
        $prescriptions = $this->basket->pendingPrescriptions($patient)
            ->orderBy('medication_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($prescriptions as $prescription) {
            $this->prescriptions->sendToFloor($prescription);
        }

        return $prescriptions->count();
    }
}
