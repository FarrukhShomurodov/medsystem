<?php

namespace App\Services\Billing;

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Exceptions\Billing\PaymentRefusedException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Takes the cashier's payment for orders of one patient: all of them are paid, or none.
 */
final class CashierPaymentService
{
    /**
     * Each order keeps the moment it was paid (paid_at): the cashier's paid list and the dashboard
     * count it on that day, whatever happens to the order afterwards.
     *
     * @param  array<string, list<int>>  $idsByType  BillableType value => order ids
     * @return int the number of orders paid
     *
     * @throws PaymentRefusedException when an order is missing, is not waiting for payment
     *                                 or belongs to another patient than the rest
     */
    public function pay(array $idsByType): int
    {
        return DB::transaction(function () use ($idsByType): int {
            $orders = $this->lockOrders($idsByType);

            $this->ensurePayable($orders);

            $paid = 0;

            foreach ($orders as $type => $rows) {
                $type = BillableType::from($type);
                $paid += $type->query()->whereKey($rows->modelKeys())->update(['status' => $type->paidStatus(), 'paid_at' => now()]);
            }

            return $paid;
        });
    }

    /**
     * Locks the orders in a fixed order (kind, then id), so that two payments never deadlock.
     *
     * @param  array<string, list<int>>  $idsByType
     * @return array<string, Collection<int, Model>>
     */
    private function lockOrders(array $idsByType): array
    {
        $orders = [];

        foreach (BillableType::cases() as $type) {
            $ids = $idsByType[$type->value] ?? [];

            if ($ids === []) {
                continue;
            }

            $rows = $type->query()
                ->whereKey($ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id', 'patient_id', 'status']);

            $missing = array_values(array_diff($ids, $rows->modelKeys()));

            if ($missing !== []) {
                throw PaymentRefusedException::missing($type, $missing);
            }

            $orders[$type->value] = $rows;
        }

        return $orders;
    }

    /**
     * @param  array<string, Collection<int, Model>>  $orders
     */
    private function ensurePayable(array $orders): void
    {
        $patients = collect($orders)->flatten(1)->pluck('patient_id')->unique();

        if ($patients->count() > 1) {
            throw PaymentRefusedException::severalPatients();
        }

        $refused = [];

        foreach ($orders as $type => $rows) {
            $type = BillableType::from($type);

            foreach ($rows as $row) {
                $status = $row->getAttribute('status');

                if (! $status instanceof ServiceStatus && ! $status instanceof ReceptionStatus && ! $status instanceof PatientMedicationStatus) {
                    throw new LogicException("The status of a {$type->value} order is not cast to its enum.");
                }

                if ($status !== $type->payableStatus()) {
                    $refused[] = "{$type->label()} №{$row->getKey()} — {$status->label()}";
                }
            }
        }

        if ($refused !== []) {
            throw PaymentRefusedException::notPayable($refused);
        }
    }
}
