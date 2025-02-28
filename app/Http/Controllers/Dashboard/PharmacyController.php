<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\PharmacyRequest;
use App\Models\Pharmacy;
use App\Models\PharmacyType;
use App\Services\PharmacyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PharmacyController
{
    protected PharmacyService $service;

    public function __construct(PharmacyService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $pharmacies = Pharmacy::query()
            ->select(['id', 'name', 'type_id'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.pharmacies.index', compact('pharmacies'));
    }

    public function create(): View
    {
        $types = PharmacyType::query()->get();
        return view('admin.pharmacies.create', compact('types'));
    }

    public function store(PharmacyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('pharmacies.index')->with('success', 'Аптека успешно добавлена!');
    }

    public function edit(Pharmacy $pharmacy): View
    {
        $types = PharmacyType::query()->get();

        return view('admin.pharmacies.edit', compact('pharmacy', 'types'));
    }

    public function update(PharmacyRequest $request, Pharmacy $pharmacy): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($pharmacy, (array)$validated);

        return redirect()->route('pharmacies.index')->with('success', 'Аптека успешно обновлена!');
    }

    public function destroy(Pharmacy $pharmacy): RedirectResponse
    {
        $this->service->destroy($pharmacy);

        return redirect()->route('pharmacies.index')->with('success', 'Аптека успешно удалена!');
    }
}
