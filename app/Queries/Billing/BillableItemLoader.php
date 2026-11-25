<?php

namespace App\Queries\Billing;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use App\Services\Billing\Amount;
use App\Services\Billing\BillableType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LogicException;
use stdClass;

/**
 * Loads orders of any kind, with what is shown about them, as BillableItems.
 *
 * The relations of the order models are not typed yet, so the helpers below take a Model
 * and check what it is.
 */
final class BillableItemLoader
{
    /** Stays well below the 65535 bind parameters PostgreSQL accepts per statement. */
    private const int IDS_PER_QUERY = 1000;

    /**
     * Loads the orders behind {type, id} rows of a union query, keeping the order of the rows.
     *
     * @param  Collection<int, stdClass>  $keys
     * @return Collection<int, BillableItem>
     */
    public function load(Collection $keys): Collection
    {
        $items = [];

        foreach ($keys->groupBy('type') as $type => $group) {
            $type = BillableType::from($type);

            foreach ($group->pluck('id')->chunk(self::IDS_PER_QUERY) as $ids) {
                foreach ($this->get($type, $type->query()->whereKey($ids->all())) as $item) {
                    $items[$item->key()] = $item;
                }
            }
        }

        // An order deleted between the two queries simply drops out of the list.
        return $keys
            ->map(fn (stdClass $key): ?BillableItem => $items["{$key->type}:{$key->id}"] ?? null)
            ->filter()
            ->values();
    }

    /**
     * @param  Builder<covariant Model>  $query  orders of the given kind
     * @return Collection<int, BillableItem>
     */
    public function get(BillableType $type, Builder $query): Collection
    {
        return $query->with($this->relations($type))->get()->toBase()->map($this->toItem(...));
    }

    /**
     * @return list<string>
     */
    private function relations(BillableType $type): array
    {
        return match ($type) {
            BillableType::Service => ['service', 'patient', 'doctor'],
            BillableType::Reception => ['service', 'patient', 'staff'],
            BillableType::Procedure => ['procedure', 'patient'],
            BillableType::Medication => ['medication', 'patient'],
        };
    }

    private function toItem(Model $order): BillableItem
    {
        return match (true) {
            $order instanceof PatientService => new BillableItem(
                type: BillableType::Service,
                id: $order->id,
                patientId: $order->patient_id,
                name: $this->nameOf($order->service),
                patientName: $this->patientName($order->patient),
                staffName: $this->staffName($order->doctor),
                price: $this->priceOf($order, $order->service),
                currency: $this->currencyOf($order),
                occurredAt: $this->dateTime($order->service_date, $order->service_time),
                status: $order->status,
            ),
            $order instanceof Reception => new BillableItem(
                type: BillableType::Reception,
                id: $order->id,
                patientId: $order->patient_id,
                name: $this->nameOf($order->service),
                patientName: $this->patientName($order->patient),
                staffName: $this->staffName($order->staff),
                price: $this->priceOf($order, $order->service),
                currency: $this->currencyOf($order),
                occurredAt: $this->dateTime($order->date, $order->start_time),
                status: $order->status,
            ),
            $order instanceof PatientProcedure => new BillableItem(
                type: BillableType::Procedure,
                id: $order->id,
                patientId: $order->patient_id,
                name: $this->nameOf($order->procedure),
                patientName: $this->patientName($order->patient),
                staffName: null,
                price: $this->priceOf($order, $order->procedure),
                currency: BillableItem::DEFAULT_CURRENCY,
                occurredAt: $this->dateTime($order->procedure_date ?? $order->created_at),
                status: $order->status,
            ),
            $order instanceof PatientMedication => new BillableItem(
                type: BillableType::Medication,
                id: $order->id,
                patientId: $order->patient_id,
                name: $this->nameOf($order->medication),
                patientName: $this->patientName($order->patient),
                staffName: null,
                // A prescription keeps the price it was ordered at.
                price: Amount::fromDecimal($order->price),
                currency: BillableItem::DEFAULT_CURRENCY,
                occurredAt: $this->dateTime($order->created_at, $order->created_at?->format('H:i')),
                status: $order->status,
            ),
            default => throw new LogicException('Not a billable order: '.$order::class),
        };
    }

    private function nameOf(?Model $entry): string
    {
        return $entry instanceof Service || $entry instanceof Procedure || $entry instanceof Medication
            ? $entry->name
            : '—';
    }

    /**
     * The price the order was placed at. An order without one (written by code older than the
     * column, after the migration filled it in) is charged the price list's current price.
     */
    private function priceOf(PatientService|Reception|PatientProcedure $order, ?Model $entry): Amount
    {
        return match (true) {
            $order->price !== null => Amount::fromDecimal($order->price),
            $entry instanceof Service || $entry instanceof Procedure => Amount::fromDecimal($entry->price),
            default => Amount::zero(),
        };
    }

    /**
     * The currency of the kept price. An order without one (see priceOf()) takes the price list's.
     */
    private function currencyOf(PatientService|Reception $order): string
    {
        return match (true) {
            $order->currency !== null => $order->currency,
            $order->service instanceof Service => $order->service->priceCurrency(),
            default => BillableItem::DEFAULT_CURRENCY,
        };
    }

    private function patientName(?Model $patient): string
    {
        return $patient instanceof Patient ? $patient->full_name : '—';
    }

    private function staffName(?Model $staff): ?string
    {
        return $staff instanceof Staff ? $staff->full_name : null;
    }

    /** "18.11.2026 10:00", or the date alone for orders without a time. */
    private function dateTime(CarbonInterface|string|null $date, ?string $time = null): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $date = Carbon::parse($date)->format('d.m.Y');

        return $time === null || $time === '' ? $date : $date.' '.substr($time, 0, 5);
    }
}
