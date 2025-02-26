<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\PharmacyTypeRequest;
use App\Models\PharmacyType;
use App\Services\PharmacyTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PharmacyTypeController
{
    protected PharmacyTypeService $service;

    public function __construct(PharmacyTypeService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $types = PharmacyType::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.pharmacy_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.pharmacy_types.create');
    }

    public function store(PharmacyTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('pharmacy.types.index')->with('success', 'Метод анализа успешно добавлен!');
    }

    public function edit(PharmacyType $type): View
    {
        return view('admin.pharmacy_types.edit', compact('type'));
    }

    public function update(PharmacyTypeRequest $request, PharmacyType $type): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($type, (array)$validated);

        return redirect()->route('pharmacy.types.index')->with('success', 'Метод анализа успешно обновлен!');
    }

    public function destroy(PharmacyType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return redirect()->route('pharmacy.types.index')->with('success', 'Метод анализа успешно удален!');
    }
}
