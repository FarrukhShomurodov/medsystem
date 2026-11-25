<?php

namespace App\Queries\Dashboard;

/**
 * The cards of the dashboard home, in page order. Each card links to one page and is shown to the
 * accounts that may open it: permissions() starts with the permission of that page's route, the
 * one its middleware checks (tests/Feature/Dashboard/DashboardCardsTest.php holds that true, as
 * NavigationTest does for the sidebar). DashboardSummary computes only the cards an account sees.
 */
enum DashboardCard: string
{
    // The figures of the day, at the top.
    case Inpatients = 'inpatients';
    case ReceptionsToday = 'receptions-today';
    case AwaitingPayment = 'awaiting-payment';
    case PaidToday = 'paid-today';

    // The lists.
    case Appointments = 'appointments';
    case Admissions = 'admissions';
    case RegistrarQueue = 'registrar-queue';

    // The lines of the stock card.
    case SupplyRequests = 'supply-requests';
    case ExpiringMainStock = 'expiring-main-stock';
    case ExpiringWardStock = 'expiring-ward-stock';
    case OutOfStock = 'out-of-stock';

    /** The name of the route the card links to. */
    public function route(): string
    {
        return match ($this) {
            self::Inpatients => 'sister.window.index',
            self::ReceptionsToday, self::Appointments => 'receptions.index',
            self::AwaitingPayment, self::PaidToday => 'cashier.index',
            self::Admissions, self::RegistrarQueue => 'patients.show',
            self::SupplyRequests => 'supply-requests.index',
            self::ExpiringMainStock => 'main.warehouse.remains',
            self::ExpiringWardStock => 'warehouse.remains',
            self::OutOfStock => 'main.warehouse.receipts.goods.index',
        };
    }

    /**
     * What an account needs to see the card: the permission of its route, and for the registrar's
     * queue the permission to confirm the orders it lists as well.
     *
     * @return non-empty-list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Inpatients => ['sister_window_show'],
            self::ReceptionsToday, self::Appointments => ['list_receptions'],
            self::AwaitingPayment, self::PaidToday => ['view_cashier'],
            self::Admissions => ['view_patients'],
            self::RegistrarQueue => ['view_patients', 'confirm_patient_registration'],
            self::SupplyRequests, self::ExpiringMainStock => ['main_warehouse_remain'],
            self::ExpiringWardStock => ['view_warehouse_remains'],
            self::OutOfStock => ['edit_main_warehouse'],
        };
    }

    /**
     * @return list<self> the stock card's lines
     */
    public static function stock(): array
    {
        return [self::SupplyRequests, self::ExpiringMainStock, self::ExpiringWardStock, self::OutOfStock];
    }

    /**
     * The cards an account sees, in page order.
     *
     * @param  callable(string): bool  $allows  whether the account holds the permission
     * @return list<self>
     */
    public static function visibleTo(callable $allows): array
    {
        return array_values(array_filter(
            self::cases(),
            function (self $card) use ($allows): bool {
                foreach ($card->permissions() as $permission) {
                    if (! $allows($permission)) {
                        return false;
                    }
                }

                return true;
            },
        ));
    }
}
