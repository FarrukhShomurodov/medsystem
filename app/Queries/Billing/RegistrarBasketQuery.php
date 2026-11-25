<?php

namespace App\Queries\Billing;

use App\Models\Patient;
use App\Models\PatientMedication;
use App\Services\Billing\BillableType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * What the registrar confirms for a patient: the orders still waiting for the registrar,
 * outpatient ones and those of an active examination map. Orders left on a closed map
 * belong to a finished stay and are not billed again. The day's queue (the dashboard) is the
 * baskets of all patients together, by the same rule.
 */
final readonly class RegistrarBasketQuery
{
    public function __construct(private BillableItemLoader $loader) {}

    /**
     * Orders of one kind waiting for the registrar.
     *
     * @return Builder<covariant Model>
     */
    public function pending(Patient $patient, BillableType $type): Builder
    {
        return $this->ofPatient($this->waitingForRegistrar($type->query(), $type), $patient, $type);
    }

    /**
     * The prescriptions waiting for the registrar.
     *
     * @return Builder<covariant PatientMedication>
     */
    public function pendingPrescriptions(Patient $patient): Builder
    {
        $type = BillableType::Medication;

        return $this->ofPatient($this->waitingForRegistrar(PatientMedication::query(), $type), $patient, $type);
    }

    /**
     * The registrar's work of a day: the orders of that day (BillableType::dateColumn()) in the
     * baskets of all patients, and the patients with the most of them.
     *
     * @param  int  $patients  how many patients to name
     */
    public function queue(CarbonInterface $day, int $patients): RegistrarQueue
    {
        $union = null;

        foreach (BillableType::cases() as $type) {
            $table = $type->table();
            $orders = $this->waitingForRegistrar($type->query(), $type)
                ->whereDate($type->dateColumn(), $day)
                ->whereNotNull("{$table}.patient_id")
                ->select("{$table}.patient_id")
                ->toBase();
            $union = $union?->unionAll($orders) ?? $orders;
        }

        // One row per patient, most orders first; the window sums run over every patient, not
        // only over the rows the limit keeps.
        $rows = DB::query()
            ->fromSub($union, 'waiting_orders')
            ->select('patient_id')
            ->selectRaw('count(*) as orders, sum(count(*)) over () as all_orders, count(*) over () as all_patients')
            ->groupBy('patient_id')
            ->orderByDesc('orders')
            ->orderBy('patient_id')
            ->limit($patients)
            ->get();

        if ($rows->isEmpty()) {
            return new RegistrarQueue(0, 0, []);
        }

        $named = Patient::query()
            ->whereKey($rows->pluck('patient_id')->all())
            ->get(['id', 'first_name', 'last_name', 'middle_name'])
            ->keyBy('id');

        $first = [];

        foreach ($rows as $row) {
            $patient = $named->get($row->patient_id);

            if ($patient !== null) {
                $first[] = ['patient' => $patient, 'orders' => (int) $row->orders];
            }
        }

        $totals = $rows->firstOrFail();

        return new RegistrarQueue((int) $totals->all_orders, (int) $totals->all_patients, $first);
    }

    /**
     * The basket, grouped by kind of order (services, receptions, procedures, prescriptions).
     *
     * @return Collection<int, BillableItem>
     */
    public function items(Patient $patient): Collection
    {
        return collect(BillableType::cases())
            ->flatMap(fn (BillableType $type): Collection => $this->loader->get($type, $this->pending($patient, $type)->orderBy('id')))
            ->values();
    }

    /**
     * Ordered and not confirmed yet, outside a stay or on an open stay of the order's own patient.
     *
     * @template TOrder of Model
     *
     * @param  Builder<covariant TOrder>  $query  orders of the given kind
     * @return Builder<covariant TOrder>
     */
    private function waitingForRegistrar(Builder $query, BillableType $type): Builder
    {
        $table = $type->table();

        return $query
            ->where("{$table}.status", $type->orderedStatus())
            ->where(fn (Builder $orders) => $orders
                ->whereNull("{$table}.examination_map_id")
                ->orWhereExists(fn (QueryBuilder $maps) => $maps
                    ->selectRaw('1')
                    ->from('examination_maps')
                    ->whereColumn('examination_maps.id', "{$table}.examination_map_id")
                    ->whereColumn('examination_maps.patient_id', "{$table}.patient_id")
                    ->where('examination_maps.is_active', true)));
    }

    /**
     * @template TOrder of Model
     *
     * @param  Builder<covariant TOrder>  $query
     * @return Builder<covariant TOrder>
     */
    private function ofPatient(Builder $query, Patient $patient, BillableType $type): Builder
    {
        return $query->where("{$type->table()}.patient_id", $patient->getKey());
    }
}
