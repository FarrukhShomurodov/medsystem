<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\TransferRequest;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Services\TransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TransferController
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(): View
    {
        $transfers = Transfer::with(['fromWarehouse', 'toPharmacy', 'medications.medication'])->paginate(10);
        return view('admin.transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        return view('admin.transfers.create', [
            'warehouses' => Warehouse::all(),
            'pharmacies' => Pharmacy::all(),
            'medications' => Medication::all()
        ]);
    }

    public function medications(Transfer $transfer): View
    {
        $medications = $transfer->medications;
        return view('admin.transfers.medications', compact('medications'));
    }

    public function store(TransferRequest $request): RedirectResponse
    {
        $this->transferService->store($request->validated());
        return redirect()->route('transfers.index')->with('success', 'Перевод выполнен успешно');
    }
}
