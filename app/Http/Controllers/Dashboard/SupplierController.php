<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SupplierController extends Controller implements HasMiddleware
{
    public function __construct(private readonly SupplierService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_suppliers', only: ['index']),
            new Middleware('permission:create_suppliers', only: ['create', 'store']),
            new Middleware('permission:edit_suppliers', only: ['edit', 'update']),
            new Middleware('permission:delete_suppliers', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $suppliers = Supplier::query()
            ->select(['id', 'name', 'phone'])
            ->with('manufacturers:id,name')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        $manufacturers = Manufacturer::query()->get();

        return view('admin.suppliers.create', compact('manufacturers'));
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('suppliers.index')->with('success', 'Поставщик успешно добавлен!');
    }

    public function edit(Supplier $supplier): View
    {
        $manufacturers = Manufacturer::query()->get();

        return view('admin.suppliers.edit', compact('supplier', 'manufacturers'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->service->update($supplier, $request->validated());

        return to_route('suppliers.index')->with('success', 'Поставщик успешно обновлен!');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->service->destroy($supplier);

        return to_route('suppliers.index')->with('success', 'Поставщик успешно удален!');
    }
}
