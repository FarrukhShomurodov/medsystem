<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Billing\PaymentRefusedException;
use App\Exports\CashierListExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CashierFilterRequest;
use App\Http\Requests\Billing\PayItemsRequest;
use App\Models\Admin;
use App\Queries\Billing\CashierItemsQuery;
use App\Services\Billing\CashierPaymentService;
use App\Services\Billing\PaymentState;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CashierController extends Controller
{
    private const int PER_PAGE = 15;

    public function __construct(private readonly CashierItemsQuery $items) {}

    public function index(CashierFilterRequest $request, #[CurrentUser(Admin::GUARD)] Admin $cashier): View
    {
        $filter = $request->filter();

        return view('admin.cashier.index', [
            'records' => $this->items->paginate($filter, self::PER_PAGE)->withQueryString(),
            'filter' => $filter,
            'paymentStates' => PaymentState::options(),
            'canPay' => $cashier->can('accept_payments'),
        ]);
    }

    public function updateAll(PayItemsRequest $request, CashierPaymentService $payments): RedirectResponse
    {
        try {
            $paid = $payments->pay($request->idsByType());
        } catch (PaymentRefusedException $refused) {
            return back()->with('error', $refused->getMessage());
        }

        return back()->with('success', "Оплачено записей: {$paid}.");
    }

    /**
     * The list as an Excel file, with the filters of the page. The orders are counted (and summed)
     * by one query first: a list longer than one file may hold sends the cashier back to narrow the
     * filter before a single order is loaded.
     */
    public function export(CashierFilterRequest $request): BinaryFileResponse|RedirectResponse
    {
        $filter = $request->filter();
        $totals = $this->items->totals($filter);
        $limit = config()->integer('medsystem.cashier_export_max_rows');

        if ($totals->orders > $limit) {
            return to_route('cashier.index', $request->query())->with(
                'error',
                "Записей для выгрузки: {$totals->orders}, а в один файл помещается не больше {$limit}. Сузьте выбор: укажите дату или пациента.",
            );
        }

        return Excel::download(new CashierListExport($this->items, $filter, $totals->sums), 'cashier_stats.xlsx');
    }
}
