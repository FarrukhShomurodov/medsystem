<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdMaterialRequest;
use App\Models\HouseholdMaterial;
use App\Services\HouseholdMaterialService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HouseholdMaterialController extends Controller
{
    protected HouseholdMaterialService $service;

    public function __construct(HouseholdMaterialService $service)
    {
        $this->middleware(['permission:view_household_materials'])->only('index');
        $this->middleware(['permission:create_household_materials'])->only(['create', 'store']);
        $this->middleware(['permission:edit_household_materials'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_household_materials'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $materials = HouseholdMaterial::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.household_materials.index', compact('materials'));
    }

    public function create(): View
    {
        return view('admin.household_materials.create');
    }

    public function store(HouseholdMaterialRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('household.materials.index')->with('success', 'Хозяйственный материал успешно добавлен!');
    }

    public function edit(HouseholdMaterial $material): View
    {
        return view('admin.household_materials.edit', compact('material'));
    }

    public function update(HouseholdMaterialRequest $request, HouseholdMaterial $material): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($material, (array)$validated);

        return redirect()->route('household.materials.index')->with('success', 'Хозяйственный материал успешно обновлен!');
    }

    public function destroy(HouseholdMaterial $material): RedirectResponse
    {
        $this->service->destroy($material);

        return redirect()->route('household.materials.index')->with('success', 'Хозяйственный материал успешно удален!');
    }
}
