<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseTypeRequest;
use App\Models\WarehouseType;
use App\Services\WarehouseTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WarehouseTypeController extends Controller
{
    protected WarehouseTypeService $service;

    public function __construct(WarehouseTypeService $service)
    {
        $this->middleware(['permission:list_warehouse_types'])->only('index');
        $this->middleware(['permission:create_warehouse_types'])->only(['create', 'store']);
        $this->middleware(['permission:edit_warehouse_types'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_warehouse_types'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $types = WarehouseType::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.warehouses_type.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.warehouses_type.create');
    }

    public function store(WarehouseTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('warehouses.type.index')->with('success', 'Тип склада успешно добавлен!');
    }

    public function edit(WarehouseType $type): View
    {
        return view('admin.warehouses_type.edit', compact('type'));
    }

    public function update(WarehouseTypeRequest $request, WarehouseType $type): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($type, (array)$validated);

        return redirect()->route('warehouses.type.index')->with('success', 'Тип склада успешно обновлен!');
    }

    public function destroy(WarehouseType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return redirect()->route('warehouses.type.index')->with('success', 'Тип склада успешно удален!');
    }
}
