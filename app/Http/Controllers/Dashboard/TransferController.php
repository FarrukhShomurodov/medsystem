<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Stock\StockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Models\WarehouseType;
use App\Services\Stock\TransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;

class TransferController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TransferService $transferService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_transfers', only: ['index']),
            new Middleware('permission:create_transfers', only: ['create', 'store']),
            new Middleware('permission:edit_transfers', only: ['edit', 'update']),
            new Middleware('permission:delete_transfers', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $transfers = Transfer::query()
            ->with(['fromWarehouse', 'toPharmacy'])
            ->withCount('medications')
            ->latest('id')
            ->paginate(10);

        return view('admin.transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        return view('admin.transfers.create', $this->formOptions());
    }

    public function store(TransferRequest $request): RedirectResponse
    {
        $this->transferService->store($request->transfer());

        return to_route('transfers.index')->with('success', 'Перевод создан и ожидает подтверждения.');
    }

    public function edit(Transfer $transfer): View|RedirectResponse
    {
        if (! $transfer->status->isEditable()) {
            return to_route('transfers.index')->with('error', 'Изменить можно только перевод в ожидании.');
        }

        $transfer->load('medications');

        return view('admin.transfers.edit', ['transfer' => $transfer, ...$this->formOptions()]);
    }

    public function update(TransferRequest $request, Transfer $transfer): RedirectResponse
    {
        try {
            $this->transferService->update($transfer, $request->transfer());
        } catch (StockException $e) {
            return to_route('transfers.index')->with('error', $e->getMessage());
        }

        return to_route('transfers.index')->with('success', 'Перевод обновлен.');
    }

    public function destroy(Transfer $transfer): RedirectResponse
    {
        try {
            $this->transferService->destroy($transfer);
        } catch (StockException $e) {
            return to_route('transfers.index')->with('error', $e->getMessage());
        }

        return to_route('transfers.index')->with('success', 'Перевод удален.');
    }

    public function medications(Transfer $transfer): View
    {
        $medications = $transfer->medications()->with('medication')->get();

        return view('admin.transfers.medications', compact('transfer', 'medications'));
    }

    /**
     * @return array{warehouses: Collection<int, Warehouse>, pharmacies: Collection<int, Pharmacy>, medications: Collection<int, Medication>}
     */
    private function formOptions(): array
    {
        return [
            'warehouses' => Warehouse::query()->where('warehouse_type_id', WarehouseType::MEDICATIONS)->orderBy('name')->get(['id', 'name']),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(['id', 'name']),
            'medications' => Medication::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
