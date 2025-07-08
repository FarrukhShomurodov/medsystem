<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainWarehouseReceiptsGoodsRequest;
use App\Models\HouseholdMaterial;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Supplier;
use App\Services\MainWarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MainWarehouseController extends Controller
{
    protected MainWarehouseService $service;

    public function __construct(MainWarehouseService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        return view();
    }

    public function receiptsGoods(): View
    {
        $medications = Medication::query()->get();
        $suppliers = Supplier::query()->get();
        $supplies = MedicalSupply::query()->get();
        $householdMaterial = HouseholdMaterial::query()->get();

        return view(
            'admin.main_warehouses.receipts_goods',
            compact('medications', 'suppliers', 'supplies', 'householdMaterial')
        );
    }

    public function receiptsGoodsStore(MainWarehouseReceiptsGoodsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->storeGoodsStore((array)$validated);

        return redirect()->back()->with(['success' => 'Товары успешно добавлены!']);
    }
}
