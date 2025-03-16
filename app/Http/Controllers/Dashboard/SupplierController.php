<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\SupplierRequest;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SupplierController
{
    protected SupplierService $service;

    public function __construct(SupplierService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $suppliers = Supplier::query()
            ->select(['id', 'name', 'phone'])
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
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('suppliers.index')->with('success', 'Поставшик успешно добавлен!');
    }

    public function edit(Supplier $supplier): View
    {
        $manufacturers = Manufacturer::query()->get();
        return view('admin.suppliers.edit', compact('supplier', 'manufacturers'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($supplier, (array)$validated);

        return redirect()->route('suppliers.index')->with('success', 'Поставшик успешно обновлен!');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->service->destroy($supplier);

        return redirect()->route('suppliers.index')->with('success', 'Поставшик успешно удален!');
    }
}
