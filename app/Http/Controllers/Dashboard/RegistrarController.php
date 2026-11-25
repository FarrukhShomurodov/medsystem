<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Stock\StockException;
use App\Exports\PatientRecordsExport;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Queries\Billing\RegistrarBasketQuery;
use App\Services\Billing\CurrencyTotals;
use App\Services\Billing\RegistrarConfirmationService;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The registrar card of the patient page: the orders waiting for the registrar.
 */
class RegistrarController extends Controller
{
    public function confirm(Patient $patient, RegistrarConfirmationService $confirmation): RedirectResponse
    {
        try {
            $confirmed = $confirmation->confirm($patient);
        } catch (StockException $shortage) {
            return back()->with('error', "Ничего не подтверждено: {$shortage->getMessage()}");
        }

        if ($confirmed === 0) {
            return back()->with('warning', 'Нет записей, ожидающих подтверждения.');
        }

        return back()->with('success', "Подтверждено записей: {$confirmed}.");
    }

    public function export(Patient $patient, RegistrarBasketQuery $basket): BinaryFileResponse
    {
        $items = $basket->items($patient);

        return Excel::download(new PatientRecordsExport($items, CurrencyTotals::of($items)), 'patient_records.xlsx');
    }
}
