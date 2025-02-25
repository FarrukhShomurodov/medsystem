<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\SupplierRequest;
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
        return view('admin.suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('suppliers.index')->with('success', 'Поставшик успешно добавлен!');
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
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
