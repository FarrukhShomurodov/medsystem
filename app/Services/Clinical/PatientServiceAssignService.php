<?php

namespace App\Services\Clinical;

use App\Enums\ServiceStatus;
use App\Exceptions\Clinical\PaidServiceException;
use App\Exceptions\Clinical\ServiceStatusTransitionException;
use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class PatientServiceAssignService
{
    /**
     * A new order joins the patient's active examination map (or the map picked on the form when
     * there is none), waits for the registrar as "assigned" and keeps the price it was ordered at.
     *
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): PatientService
    {
        $patient = Patient::query()->findOrFail((int) $validated['patient_id']);

        return PatientService::query()->create([
            ...$validated,
            'examination_map_id' => $patient->activeExaminationMap()->value('id') ?? $validated['examination_map_id'] ?? null,
            'status' => ServiceStatus::Assigned,
            ...$this->priceOf(Service::query()->findOrFail((int) $validated['service_id'])),
        ]);
    }

    /**
     * Editing an order changes its details; its status moves only through changeStatus(), the
     * registrar and the cashier. Once paid, the order records what was paid for and by whom, so
     * its service and patient stay; the result and the other details can still be filled in.
     * Another service is charged at its own current price.
     *
     * @param  array<string, mixed>  $validated
     *
     * @throws PaidServiceException
     */
    public function update(PatientService $service, array $validated): PatientService
    {
        return DB::transaction(function () use ($service, $validated): PatientService {
            $locked = PatientService::query()->lockForUpdate()->findOrFail($service->id);
            $reassigned = (int) $validated['service_id'] !== $locked->service_id
                || (int) $validated['patient_id'] !== $locked->patient_id;

            if ($reassigned && $locked->isPaid()) {
                throw PaidServiceException::reassigned();
            }

            $locked->fill($validated);

            if ($locked->isDirty('service_id')) {
                $locked->fill($this->priceOf(Service::query()->findOrFail($locked->service_id)));
            }

            $locked->save();

            return $locked;
        });
    }

    /**
     * A repeated request for the current status changes nothing.
     *
     * @throws ServiceStatusTransitionException
     */
    public function changeStatus(PatientService $service, ServiceStatus $next): PatientService
    {
        return DB::transaction(function () use ($service, $next): PatientService {
            $locked = PatientService::query()->lockForUpdate()->findOrFail($service->id);
            $current = $locked->status;

            if ($current === $next) {
                return $locked;
            }

            if (! $current->canTransitionTo($next)) {
                throw ServiceStatusTransitionException::between($current, $next);
            }

            $locked->update(['status' => $next]);

            return $locked;
        });
    }

    /**
     * A paid order is the record of the payment; an unpaid one can be deleted or cancelled.
     *
     * @throws PaidServiceException
     */
    public function destroy(PatientService $service): void
    {
        DB::transaction(function () use ($service): void {
            $locked = PatientService::query()->lockForUpdate()->findOrFail($service->id);

            if ($locked->isPaid()) {
                throw PaidServiceException::deleted();
            }

            $locked->delete();
        });
    }

    /**
     * What the order keeps of the price list entry: its current price and that price's currency.
     *
     * @return array{price: string, currency: string}
     */
    private function priceOf(Service $service): array
    {
        return ['price' => $service->price, 'currency' => $service->priceCurrency()];
    }
}
