<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PatientMedicationStatus;
use App\Exceptions\Stock\StockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientMedicationRequest;
use App\Models\Medication;
use App\Models\PatientMedication;
use App\Services\Clinical\DangerousCombinationCheck;
use App\Services\Clinical\PatientMedicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PatientMedicationController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly PatientMedicationService $service,
        private readonly DangerousCombinationCheck $combinations,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_patient_medication', only: ['store']),
            new Middleware('permission:edit_patient_medication', only: ['edit', 'update']),
        ];
    }

    public function store(PatientMedicationRequest $request): RedirectResponse
    {
        try {
            $prescriptions = $this->service->store($request->validated());
        } catch (StockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return $this->withCombinationWarning(back(), $prescriptions)
            ->with('success', 'Назначения добавлены в лист назначений.');
    }

    public function edit(PatientMedication $medication): View|RedirectResponse
    {
        $status = $medication->status;

        if ($status->isFinal()) {
            return to_route('patients.show', $medication->patient_id)
                ->with('error', "Назначение в статусе «{$status->label()}» изменить нельзя.");
        }

        $medication->load(['medication.composition', 'compatibleMedications']);

        return view('admin.patients.medication.edit', [
            'medication' => $medication,
            'allMedications' => Medication::query()->with('composition')->orderBy('name')->get(),
            // Once the drug has left the warehouse, the drug and its quantity are fixed.
            'locked' => $status !== PatientMedicationStatus::Ordered,
        ]);
    }

    public function update(PatientMedicationRequest $request, PatientMedication $medication): RedirectResponse
    {
        try {
            $prescription = $this->service->update($medication, $request->validated());
        } catch (StockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return $this->withCombinationWarning(to_route('patients.show', $prescription->patient_id), new Collection([$prescription]))
            ->with('success', 'Назначение обновлено.');
    }

    /**
     * @param  Collection<int, PatientMedication>  $prescriptions
     */
    private function withCombinationWarning(RedirectResponse $response, Collection $prescriptions): RedirectResponse
    {
        $found = $this->combinations->forPrescriptions($prescriptions);

        return $found->isEmpty() ? $response : $response->with('warning', $this->combinations->warning($found));
    }
}
