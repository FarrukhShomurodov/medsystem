<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\MedicationRequest;
use App\Models\Manufacturer;
use App\Models\Medication;
use App\Models\MedicationGroup;
use App\Models\Supplier;
use App\Services\MedicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicationController
{
    protected MedicationService $service;

    public function __construct(MedicationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): View
    {
        $medications = Medication::query()
            ->select(['id', 'name', 'release_form', 'dosage', 'manufacturer_id', 'group_id', 'registration_number'])
            ->orderBy('id', 'desc')
            ->when($request->filled('active_substance_name'), function ($q) use ($request) {
                $q->whereHas('composition', function ($q) use ($request) {
                    $q->where('active_substance_name', $request->input('active_substance_name'));
                });
            })
            ->when($request->filled('group_id'), function ($q) use ($request) {
                $q->where('group_id', $request->input('group_id'));
            })
            ->when($request->filled('manufacturer_id'), function ($q) use ($request) {
                $q->where('manufacturer_id', $request->input('manufacturer_id'));
            })
            ->paginate(10);

        $manufacturers = Manufacturer::query()->get();
        $groups = MedicationGroup::query()->get();

        return view('admin.medications.index', compact('medications', 'manufacturers', 'groups'));
    }

    public function show(Medication $medication): View
    {
        return view('admin.medications.show', compact('medication'));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()->get();
        $manufacturers = Manufacturer::query()->get();
        $groups = MedicationGroup::query()->get();

        return view('admin.medications.create', compact('suppliers', 'manufacturers', 'groups'));
    }

    public function store(MedicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('medications.index')->with('success', 'Препарат успешно добавлен!');
    }

    public function edit(Medication $medication): View
    {
        $suppliers = Supplier::query()->get();
        $manufacturers = Manufacturer::query()->get();
        $groups = MedicationGroup::query()->get();

        return view('admin.medications.edit', compact('medication', 'suppliers', 'manufacturers', 'groups'));
    }

    public function update(MedicationRequest $request, Medication $medication): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($medication, (array)$validated);

        return redirect()->route('medications.index')->with('success', 'Препарат успешно обновлен!');
    }

    public function destroy(Medication $medication): RedirectResponse
    {
        $this->service->destroy($medication);

        return redirect()->route('medications.index')->with('success', 'Препарат успешно удален!');
    }
}
