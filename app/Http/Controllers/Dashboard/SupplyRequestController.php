<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Stock\StockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\RejectSupplyRequest;
use App\Http\Requests\StoreSupplyRequest;
use App\Models\SupplyRequest;
use App\Services\Stock\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplyRequestController extends Controller
{
    public function __construct(private readonly SupplyRequestService $service) {}

    public function index(): View
    {
        $requests = SupplyRequest::query()
            ->with(['warehouse', 'items.supplyable'])
            ->latest('id')
            ->paginate(20);

        return view('admin.main_warehouses.orders', compact('requests'));
    }

    public function approve(SupplyRequest $supplyRequest): RedirectResponse
    {
        try {
            $this->service->approve($supplyRequest);
        } catch (StockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Заказ одобрен: товары перемещены на склад отделения.');
    }

    public function reject(RejectSupplyRequest $request, SupplyRequest $supplyRequest): RedirectResponse
    {
        try {
            $this->service->reject($supplyRequest, $request->reason());
        } catch (StockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Заказ отклонён: товары не перемещались.');
    }

    public function store(StoreSupplyRequest $request): RedirectResponse
    {
        $this->service->store($request->supply());

        return to_route('warehouse.remains')->with('success', 'Заказ отправлен на главный склад.');
    }
}
