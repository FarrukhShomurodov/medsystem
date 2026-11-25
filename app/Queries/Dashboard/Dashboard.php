<?php

namespace App\Queries\Dashboard;

use App\Models\ExaminationMap;
use App\Models\Reception;
use App\Queries\Billing\OrderTotals;
use App\Queries\Billing\RegistrarQueue;
use App\Queries\Stock\ExpiryWarnings;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * The dashboard home as one account sees it (DashboardSummary). A figure is null when the account
 * does not see its card.
 */
final readonly class Dashboard
{
    /**
     * @param  list<DashboardCard>  $cards  the cards the account sees, in page order
     * @param  int|null  $inpatients  patients with an open stay, in the account's nurse window
     * @param  bool  $inpatientsOfAllDepartments  false: of the department the account works in
     * @param  Collection<int, Reception>|null  $appointments  the first of the day's booked appointments
     * @param  Collection<int, ExaminationMap>|null  $admissions  the latest open stays
     */
    public function __construct(
        public CarbonImmutable $today,
        public array $cards,
        public ?int $inpatients = null,
        public bool $inpatientsOfAllDepartments = true,
        public ?ReceptionsToday $receptions = null,
        public ?Collection $appointments = null,
        public ?OrderTotals $awaitingPayment = null,
        public ?OrderTotals $paid = null,
        public ?Collection $admissions = null,
        public ?RegistrarQueue $registrarQueue = null,
        public ?int $pendingSupplyRequests = null,
        public ?ExpiryWarnings $mainWarehouseExpiry = null,
        public ?ExpiryWarnings $wardWarehouseExpiry = null,
        public ?int $outOfStock = null,
    ) {}

    public function shows(DashboardCard ...$cards): bool
    {
        foreach ($cards as $card) {
            if (in_array($card, $this->cards, true)) {
                return true;
            }
        }

        return false;
    }

    /** An account that may open the dashboard and nothing it reports on. */
    public function isEmpty(): bool
    {
        return $this->cards === [];
    }
}
