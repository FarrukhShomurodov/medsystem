<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplyRequest;
use App\Models\SupplyRequest;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SupplyRequestController extends Controller
{
    protected $service;

    public function __construct(SupplyRequestService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $requests = SupplyRequest::with(['warehouse'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.main_warehouses.orders', compact('requests'));
    }


    public function approve($id)
    {
        $mainWarehouseId = 1;
        $errors = [];

        DB::transaction(function () use ($id, $mainWarehouseId, &$errors) {
            $request = SupplyRequest::with('items.supplyable', 'warehouse')->findOrFail($id);
            $warehouse = $request->warehouse;

            foreach ($request->items as $item) {
                $modelClass = $item->supplyable_type;
                $itemId = $item->supplyable_id;
                $quantity = $item->requested_quantity;

                switch ($modelClass) {
                    case \App\Models\Medication::class:
                        $mainTable = DB::table('main_warehouse_medications');
                        $existing = $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('medication_id', $itemId)
                            ->first();

                        if (!$existing || $existing->quantity < $quantity) {
                            $errors[] = "Недостаточно медикамента ID: $itemId на главном складе.";
                            break;
                        }

                        $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('medication_id', $itemId)
                            ->update(['quantity' => $existing->quantity - $quantity, 'updated_at' => now()]);

                        DB::table('warehouse_accountings')->insert(
                            [
                                'warehouse_id' => $warehouse->id,
                                'medication_id' => $itemId,
                                'quantity' => $quantity,
                                'expiration_date' => $existing->expiration_date,
                                'updated_at' => now(),
                            ]
                        );
                        break;

                    case \App\Models\MedicalSupply::class:
                        $mainTable = DB::table('main_warehouse_medical_supplies');
                        $existing = $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('supply_id', $itemId)
                            ->first();

                        if (!$existing || $existing->quantity < $quantity) {
                            $errors[] = "Недостаточно мед. расходника ID: $itemId на главном складе.";
                            break;
                        }

                        $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('supply_id', $itemId)
                            ->update(['quantity' => $existing->quantity - $quantity, 'updated_at' => now()]);

                        DB::table('warehouse_medical_supplies')->updateOrInsert(
                            ['warehouse_id' => $warehouse->id, 'supply_id' => $itemId],
                            ['quantity' => DB::raw("quantity + $quantity"), 'updated_at' => now()]
                        );
                        break;

                    case \App\Models\HouseholdMaterial::class:
                        $mainTable = DB::table('main_warehouse_household_materials');
                        $existing = $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('household_material_id', $itemId)
                            ->first();

                        if (!$existing || $existing->quantity < $quantity) {
                            $errors[] = "Недостаточно хоз. материала ID: $itemId на главном складе.";
                            break;
                        }

                        $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('household_material_id', $itemId)
                            ->update(['quantity' => $existing->quantity - $quantity, 'updated_at' => now()]);

                        DB::table('warehouse_household_materials')->updateOrInsert(
                            ['warehouse_id' => $warehouse->id, 'household_material_id' => $itemId],
                            ['quantity' => DB::raw("quantity + $quantity"), 'updated_at' => now()]
                        );
                        break;

                    default:
                        $errors[] = "Неизвестный тип: $modelClass";
                }
            }

            if (empty($errors)) {
                $request->update(['status' => 'approved']);
            }
        });

        if (!empty($errors)) {
            return redirect()->back()->withErrors(implode("\n", $errors));
        }

        return redirect()->back()->with('success', 'Заказ одобрен и материалы перемещены на склад');
    }

    public function store(StoreSupplyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->store((array)$validated);

        return redirect()->back()->with('success', 'Заказ успешно создан!');
    }
}
