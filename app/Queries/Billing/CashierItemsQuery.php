<?php

namespace App\Queries\Billing;

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Queries\LikePattern;
use App\Services\Billing\Amount;
use App\Services\Billing\BillableType;
use App\Services\Billing\CurrencyTotals;
use App\Services\Billing\PaymentState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use stdClass;

/**
 * The orders of all patients at the cashier: one list over the four kinds of orders,
 * filtered, sorted and paginated by the database.
 */
final readonly class CashierItemsQuery
{
    public function __construct(private BillableItemLoader $loader) {}

    /**
     * @return LengthAwarePaginator<int, BillableItem>
     */
    public function paginate(CashierFilter $filter, int $perPage): LengthAwarePaginator
    {
        $page = $this->keys($filter)->paginate($perPage);

        return $page->setCollection($this->loader->load($page->getCollection()));
    }

    /**
     * The orders of the list for an export, a chunk at a time: the keys are read a page per
     * query (the sort ends on type and id, so no page repeats or skips an order) and each page is
     * loaded on its own, so memory holds one chunk of orders, never the whole list.
     *
     * @param  positive-int  $chunkSize
     * @return LazyCollection<int, BillableItem>
     */
    public function lazy(CashierFilter $filter, int $chunkSize = 1000): LazyCollection
    {
        return $this->keys($filter)
            ->lazy($chunkSize)
            ->chunk($chunkSize)
            ->flatMap(fn (LazyCollection $keys): Collection => $this->loader->load($keys->values()->collect()));
    }

    /**
     * How many orders the list holds and what they come to per currency, summed by the database
     * in one query. Prices are read as BillableItemLoader reads them (the price kept on the order,
     * else the price list's), so the sums equal CurrencyTotals::of() over the loaded list.
     */
    public function totals(CashierFilter $filter): OrderTotals
    {
        $union = null;

        foreach (BillableType::cases() as $type) {
            $amounts = $this->amountsOf($type, $filter);
            $union = $union?->unionAll($amounts) ?? $amounts;
        }

        $rows = DB::query()
            ->fromSub($union, 'billable_amounts')
            ->select('currency')
            ->selectRaw('count(*) as orders, sum(amount) as amount')
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        $sums = $rows
            ->sortBy(fn (stdClass $row): int => $row->currency === BillableItem::DEFAULT_CURRENCY ? 0 : 1)
            ->mapWithKeys(fn (stdClass $row): array => [(string) $row->currency => Amount::fromDecimal($row->amount)])
            ->all();

        return new OrderTotals((int) $rows->sum('orders'), CurrencyTotals::fromSums($sums));
    }

    /**
     * {type, id} of the matching orders, newest first.
     */
    private function keys(CashierFilter $filter): QueryBuilder
    {
        $union = null;

        foreach (BillableType::cases() as $type) {
            $keys = $this->keysOf($type, $filter);
            $union = $union?->unionAll($keys) ?? $keys;
        }

        return DB::query()
            ->fromSub($union, 'billable_items')
            ->select(['type', 'id'])
            ->orderByRaw('occurred_at desc nulls last')
            ->orderBy('type')
            ->orderByDesc('id');
    }

    private function keysOf(BillableType $type, CashierFilter $filter): QueryBuilder
    {
        $table = $type->table();

        return $this->matching($type, $filter)
            // The type is an enum value, never user input.
            ->selectRaw("'{$type->value}'::text as type, {$table}.id, {$this->occurredAt($type)} as occurred_at")
            ->toBase();
    }

    /**
     * The price and currency of each matching order, the way BillableItemLoader::priceOf() and
     * currencyOf() read them: the kept price, else the price list's; the kept currency, else the
     * price list's code (trimmed, upper case), else the default one. Procedures and prescriptions
     * are priced in the default currency.
     */
    private function amountsOf(BillableType $type, CashierFilter $filter): QueryBuilder
    {
        $table = $type->table();
        // A constant of the code, never user input.
        $default = "'".BillableItem::DEFAULT_CURRENCY."'::text";
        $orders = $this->matching($type, $filter);

        [$query, $amount, $currency] = match ($type) {
            BillableType::Service, BillableType::Reception => [
                $orders->leftJoin('services', 'services.id', '=', "{$table}.service_id"),
                "coalesce({$table}.price, services.price, 0)",
                "coalesce({$table}.currency, nullif(upper(btrim(services.currency)), ''), {$default})",
            ],
            BillableType::Procedure => [
                $orders->leftJoin('procedures', 'procedures.id', '=', "{$table}.procedure_id"),
                "coalesce({$table}.price, procedures.price, 0)",
                $default,
            ],
            BillableType::Medication => [$orders, "coalesce({$table}.price, 0)", $default],
        };

        return $query->selectRaw("{$amount} as amount, {$currency} as currency")->toBase();
    }

    /**
     * The orders of one kind the filter keeps.
     *
     * Waiting for payment: the payable status, on the order's own day. Paid: an order the cash desk
     * recorded a payment for (paid_at), on the day it was paid, whatever happened to it since; an
     * order paid before paid_at existed counts by its status (paid or later) on its own day.
     *
     * @return Builder<covariant Model>
     */
    private function matching(BillableType $type, CashierFilter $filter): Builder
    {
        $table = $type->table();
        $query = $type->query();

        if ($filter->state === PaymentState::Paid) {
            $paidStatuses = array_map(fn (ServiceStatus|ReceptionStatus|PatientMedicationStatus $status): string => $status->value, $type->paidStatuses());

            $query->where(fn (Builder $paid) => $paid->whereNotNull("{$table}.paid_at")->orWhereIn("{$table}.status", $paidStatuses))
                // The column names are constants of the code, never user input.
                ->when($filter->date, fn (Builder $paid) => $paid->whereDate(DB::raw("coalesce({$table}.paid_at, {$type->dateColumn()})"), $filter->date));
        } else {
            $query->where("{$table}.status", $type->payableStatus()->value)
                ->when($filter->date, fn (Builder $pending) => $pending->whereDate($type->dateColumn(), $filter->date));
        }

        return $query
            ->when($filter->firstName, fn (Builder $query, string $name) => $query->whereHas(
                'patient',
                fn (Builder $patient) => $patient->whereLike('first_name', LikePattern::contains($name)),
            ))
            ->when($filter->lastName, fn (Builder $query, string $name) => $query->whereHas(
                'patient',
                fn (Builder $patient) => $patient->whereLike('last_name', LikePattern::contains($name)),
            ));
    }

    /**
     * The moment the list is sorted by: the date and time shown for the order.
     *
     * @return literal-string
     */
    private function occurredAt(BillableType $type): string
    {
        return match ($type) {
            BillableType::Service => "(patient_services.service_date + coalesce(patient_services.service_time, time '00:00'))",
            BillableType::Reception => '(receptions.date + receptions.start_time)',
            BillableType::Procedure => 'coalesce(patient_procedures.procedure_date::timestamp, patient_procedures.created_at)',
            BillableType::Medication => 'patient_medications.created_at',
        };
    }
}
