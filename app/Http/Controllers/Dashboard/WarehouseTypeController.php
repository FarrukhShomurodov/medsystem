<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseTypeRequest;
use App\Models\WarehouseType;
use App\Services\WarehouseTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WarehouseTypeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly WarehouseTypeService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_warehouse_types', only: ['index']),
            new Middleware('permission:create_warehouse_types', only: ['create', 'store']),
            new Middleware('permission:edit_warehouse_types', only: ['edit', 'update']),
            new Middleware('permission:delete_warehouse_types', only: ['destroy']),
        ];
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
        $this->service->store($request->validated());

        return to_route('warehouses.type.index')->with('success', 'Тип склада успешно добавлен!');
    }

    public function edit(WarehouseType $type): View
    {
        return view('admin.warehouses_type.edit', compact('type'));
    }

    public function update(WarehouseTypeRequest $request, WarehouseType $type): RedirectResponse
    {
        $this->service->update($type, $request->validated());

        return to_route('warehouses.type.index')->with('success', 'Тип склада успешно обновлен!');
    }

    public function destroy(WarehouseType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return to_route('warehouses.type.index')->with('success', 'Тип склада успешно удален!');
    }
}
