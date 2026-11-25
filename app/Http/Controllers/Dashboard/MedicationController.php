<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationRequest;
use App\Http\Requests\Stock\MedicationFilterRequest;
use App\Models\Manufacturer;
use App\Models\Medication;
use App\Models\MedicationGroup;
use App\Models\Supplier;
use App\Services\MedicationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class MedicationController extends Controller implements HasMiddleware
{
    public function __construct(private readonly MedicationService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_medications', only: ['index']),
            new Middleware('permission:view_medications', only: ['show']),
            new Middleware('permission:create_medications', only: ['create', 'store']),
            new Middleware('permission:edit_medications', only: ['edit', 'update']),
            new Middleware('permission:delete_medications', only: ['destroy']),
        ];
    }

    public function index(MedicationFilterRequest $request): View
    {
        $medications = Medication::query()
            ->select(['id', 'name', 'release_form', 'dosage', 'manufacturer_id', 'group_id', 'registration_number'])
            ->with(['manufacturer:id,name', 'group:id,name'])
            ->orderBy('id', 'desc')
            ->when($request->activeSubstance(), fn (Builder $query, string $substance) => $query
                ->whereHas('composition', fn (Builder $composition) => $composition->where('active_substance_name', $substance)))
            ->when($request->groupId(), fn (Builder $query, int $groupId) => $query->where('group_id', $groupId))
            ->when($request->manufacturerId(), fn (Builder $query, int $manufacturerId) => $query->where('manufacturer_id', $manufacturerId))
            ->paginate(10);

        $manufacturers = Manufacturer::query()->get();
        $groups = MedicationGroup::query()->get();

        return view('admin.medications.index', compact('medications', 'manufacturers', 'groups'));
    }

    public function show(Medication $medication): View
    {
        $medication->load(['group', 'manufacturer', 'composition.additionalSubstance', 'application', 'storage', 'licenseAndCertification', 'additionalData']);

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
        $this->service->store($request->validated());

        return to_route('medications.index')->with('success', 'Препарат успешно добавлен!');
    }

    public function edit(Medication $medication): View
    {
        $medication->load(['composition.additionalSubstance', 'application', 'storage', 'additionalData']);
        $suppliers = Supplier::query()->get();
        $manufacturers = Manufacturer::query()->get();
        $groups = MedicationGroup::query()->get();

        return view('admin.medications.edit', compact('medication', 'suppliers', 'manufacturers', 'groups'));
    }

    public function update(MedicationRequest $request, Medication $medication): RedirectResponse
    {
        $this->service->update($medication, $request->validated());

        return to_route('medications.index')->with('success', 'Препарат успешно обновлен!');
    }

    public function destroy(Medication $medication): RedirectResponse
    {
        $this->service->destroy($medication);

        return to_route('medications.index')->with('success', 'Препарат успешно удален!');
    }
}
