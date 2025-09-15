<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Services\TransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->middleware(['permission:list_transfers'])->only('index');
        $this->middleware(['permission:create_transfers'])->only(['create', 'store']);
        $this->middleware(['permission:edit_transfers'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_transfers'])->only('destroy');

        $this->transferService = $transferService;
    }

    public function index(): View
    {
        $transfers = Transfer::with(['fromWarehouse', 'toPharmacy', 'medications.medication'])->paginate(10);
        return view('admin.transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::query()->get();
        $pharmacies = Pharmacy::query()->get();
        $medications = Medication::query()->get();

        return view('admin.transfers.create', compact('warehouses', 'pharmacies', 'medications'));
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
