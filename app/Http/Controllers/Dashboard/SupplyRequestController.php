<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplyRequest;
use App\Models\SupplyRequest;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            ->get();

        return view('admin.main_warehouses.orders', compact('requests'));
    }


    public function approve($id)
    {
        $mainWarehouseId = 1;

        DB::transaction(function () use ($id, $mainWarehouseId) {
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

                        if ($existing) {
                            $mainTable->where('main_warehouse_id', $mainWarehouseId)
                                ->where('medication_id', $itemId)
                                ->update([
                                    'quantity' => $existing->quantity + $quantity,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $mainTable->insert([
                                'main_warehouse_id' => $mainWarehouseId,
                                'medication_id' => $itemId,
                                'quantity' => $quantity,
                                'expiration_date' => now()->addMonths(6), // временно, если нет данных
                                'date_of_receipt' => now(),
                                'batch_number' => Str::uuid(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // Маленький склад
                        $smallTable = DB::table('warehouse_accountings');
                        $existingSmall = $smallTable->where('warehouse_id', $warehouse->id)
                            ->where('medication_id', $itemId)
                            ->first();

                        if ($existingSmall) {
                            $smallTable->where('warehouse_id', $warehouse->id)
                                ->where('medication_id', $itemId)
                                ->update([
                                    'quantity' => $existingSmall->quantity + $quantity,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $smallTable->insert([
                                'warehouse_id' => $warehouse->id,
                                'medication_id' => $itemId,
                                'quantity' => $quantity,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        break;

                    case \App\Models\MedicalSupply::class:
                        // Главный склад
                        $mainTable = DB::table('main_warehouse_medical_supplies');
                        $existing = $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('supply_id', $itemId)
                            ->first();

                        if ($existing) {
                            $mainTable->where('main_warehouse_id', $mainWarehouseId)
                                ->where('supply_id', $itemId)
                                ->update([
                                    'quantity' => $existing->quantity + $quantity,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $mainTable->insert([
                                'main_warehouse_id' => $mainWarehouseId,
                                'supply_id' => $itemId,
                                'quantity' => $quantity,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // Маленький склад
                        $smallTable = DB::table('warehouse_medical_supplies');
                        $existingSmall = $smallTable->where('warehouse_id', $warehouse->id)
                            ->where('supply_id', $itemId)
                            ->first();

                        if ($existingSmall) {
                            $smallTable->where('warehouse_id', $warehouse->id)
                                ->where('supply_id', $itemId)
                                ->update([
                                    'quantity' => $existingSmall->quantity + $quantity,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $smallTable->insert([
                                'warehouse_id' => $warehouse->id,
                                'supply_id' => $itemId,
                                'quantity' => $quantity,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        break;


                    case \App\Models\HouseholdMaterial::class:
                        // Главный склад
                        $mainTable = DB::table('main_warehouse_household_materials');
                        $existing = $mainTable->where('main_warehouse_id', $mainWarehouseId)
                            ->where('household_material_id', $itemId)
                            ->first();

                        if ($existing) {
                            $mainTable->where('main_warehouse_id', $mainWarehouseId)
                                ->where('household_material_id', $itemId)
                                ->update([
                                    'quantity' => $existing->quantity + $quantity,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $mainTable->insert([
                                'main_warehouse_id' => $mainWarehouseId,
                                'household_material_id' => $itemId,
                                'quantity' => $quantity,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // Маленький склад
                        $smallTable = DB::table('warehouse_household_materials');
                        $existingSmall = $smallTable->where('warehouse_id', $warehouse->id)
                            ->where('household_material_id', $itemId)
                            ->first();

                        if ($existingSmall) {
                            $smallTable->where('warehouse_id', $warehouse->id)
                                ->where('household_material_id', $itemId)
                                ->update([
                                    'quantity' => $existingSmall->quantity + $quantity,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $smallTable->insert([
                                'warehouse_id' => $warehouse->id,
                                'household_material_id' => $itemId,
                                'quantity' => $quantity,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        break;

                    default:
                        throw new \Exception("Неизвестный тип: $modelClass");
                }
            }

            $request->update(['status' => 'approved']);
        });

        return redirect()->back()->with('success', 'Заказ принят, материалы добавлены на склад');
    }

    public function store(StoreSupplyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->store((array)$validated);

        return redirect()->back()->with('success', 'Заказ успешно создан!');
    }
}
