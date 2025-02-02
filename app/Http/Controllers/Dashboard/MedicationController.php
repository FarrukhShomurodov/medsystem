<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\MedicationRequest;
use App\Models\Medication;
use App\Services\MedicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicationController
{
    protected MedicationService $service;

    public function __construct(MedicationService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $medications = Medication::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.medications.index', compact('medications'));
    }

    public function create(): View
    {
        return view('admin.medications.create');
    }

    public function store(MedicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('medications.index')->with('success', 'Препарат успешно добавлен!');
    }

    public function edit(Medication $medication): View
    {
        return view('admin.medications.edit', compact('medication'));
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
