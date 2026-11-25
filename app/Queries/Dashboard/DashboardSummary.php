<?php

namespace App\Queries\Dashboard;

use App\Enums\ReceptionStatus;
use App\Models\Admin;
use App\Models\ExaminationMap;
use App\Models\Reception;
use App\Models\Staff;
use App\Queries\Billing\CashierFilter;
use App\Queries\Billing\CashierItemsQuery;
use App\Queries\Billing\RegistrarBasketQuery;
use App\Queries\Patients\NursePatientsQuery;
use App\Queries\Stock\StockAlertsQuery;
use App\Services\Billing\PaymentState;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

/**
 * The read model of the dashboard home: the figures of the day for one account.
 *
 * Only the cards the account sees are computed, each with a fixed number of queries (aggregates
 * in the database, short lists with their relations eager-loaded), so the page costs the same
 * however busy the hospital is. Every figure is read through the query of the page its card links
 * to, or by the same rule, so the card and the page agree: the nurse window's patients, the cashier
 * list of the day, the registrar's basket, the remains pages' expiry warning.
 */
final readonly class DashboardSummary
{
    /** Rows of the day's appointments list. */
    public const int APPOINTMENTS = 8;

    /** Rows of the latest admissions list. */
    public const int ADMISSIONS = 5;

    /** Patients named in the registrar's queue. */
    public const int QUEUE = 5;

    public function __construct(
        private CashierItemsQuery $cashier,
        private RegistrarBasketQuery $registrar,
        private StockAlertsQuery $stock,
    ) {}

    public function for(Admin $viewer, CarbonImmutable $today): Dashboard
    {
        $cards = DashboardCard::visibleTo(fn (string $permission): bool => $viewer->can($permission));
        $shows = fn (DashboardCard ...$any): bool => array_filter($any, fn (DashboardCard $card): bool => in_array($card, $cards, true)) !== [];

        // A doctor's reception list shows their own appointments (ReceptionController::index): so do the cards.
        $doctor = $shows(DashboardCard::ReceptionsToday, DashboardCard::Appointments) ? $viewer->doctorProfile() : null;
        $ward = $shows(DashboardCard::Inpatients) ? NursePatientsQuery::for($viewer) : null;

        return new Dashboard(
            today: $today,
            cards: $cards,
            inpatients: $ward?->count(),
            inpatientsOfAllDepartments: $ward?->coversAllDepartments() ?? true,
            receptions: $shows(DashboardCard::ReceptionsToday, DashboardCard::Appointments) ? $this->receptions($today, $doctor) : null,
            appointments: $shows(DashboardCard::Appointments) ? $this->appointments($today, $doctor) : null,
            awaitingPayment: $shows(DashboardCard::AwaitingPayment) ? $this->cashier->totals(new CashierFilter(date: $today)) : null,
            paid: $shows(DashboardCard::PaidToday) ? $this->cashier->totals(new CashierFilter(state: PaymentState::Paid, date: $today)) : null,
            admissions: $shows(DashboardCard::Admissions) ? $this->admissions() : null,
            registrarQueue: $shows(DashboardCard::RegistrarQueue) ? $this->registrar->queue($today, self::QUEUE) : null,
            pendingSupplyRequests: $shows(DashboardCard::SupplyRequests) ? $this->stock->pendingSupplyRequests() : null,
            mainWarehouseExpiry: $shows(DashboardCard::ExpiringMainStock) ? $this->stock->expiryInMainWarehouse($today) : null,
            wardWarehouseExpiry: $shows(DashboardCard::ExpiringWardStock) ? $this->stock->expiryInWardWarehouses($today) : null,
            outOfStock: $shows(DashboardCard::OutOfStock) ? $this->stock->outOfStockInMainWarehouse($today) : null,
        );
    }

    private function receptions(CarbonImmutable $today, ?Staff $doctor): ReceptionsToday
    {
        $byStatus = [];

        $rows = $this->receptionsOn($today, $doctor)
            ->toBase()
            ->select('status')
            ->selectRaw('count(*) as appointments')
            ->groupBy('status')
            ->get();

        foreach ($rows as $row) {
            /** @var stdClass $row */
            $byStatus[(string) $row->status] = (int) $row->appointments;
        }

        return new ReceptionsToday($byStatus, ownOnly: $doctor !== null);
    }

    /**
     * The day's booked appointments in the order of the day.
     *
     * @return Collection<int, Reception>
     */
    private function appointments(CarbonImmutable $today, ?Staff $doctor): Collection
    {
        return $this->receptionsOn($today, $doctor)
            ->select(['id', 'patient_id', 'staff_id', 'start_time', 'end_time', 'status'])
            ->with(['patient:id,first_name,last_name,middle_name', 'staff:id,name,last_name,middle_name'])
            ->where('status', '!=', ReceptionStatus::Cancelled)
            ->orderBy('start_time')
            ->orderBy('id')
            ->limit(self::APPOINTMENTS)
            ->get();
    }

    /**
     * The open stays, the latest admission first.
     *
     * @return Collection<int, ExaminationMap>
     */
    private function admissions(): Collection
    {
        return ExaminationMap::query()
            ->select(['id', 'patient_id', 'staff_id', 'department_id', 'admission_date', 'is_outpatient'])
            ->with(['patient:id,first_name,last_name,middle_name', 'department:id,name', 'doctor:id,name,last_name,middle_name'])
            ->active()
            ->orderByDesc('admission_date')
            ->orderByDesc('id')
            ->limit(self::ADMISSIONS)
            ->get();
    }

    /**
     * @return Builder<Reception>
     */
    private function receptionsOn(CarbonImmutable $day, ?Staff $doctor): Builder
    {
        return Reception::query()
            ->where('date', $day->toDateString())
            ->when($doctor, fn (Builder $query, Staff $doctor) => $query->where('staff_id', $doctor->id));
    }
}
