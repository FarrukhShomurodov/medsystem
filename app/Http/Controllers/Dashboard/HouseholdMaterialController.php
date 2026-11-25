<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdMaterialRequest;
use App\Models\HouseholdMaterial;
use App\Services\HouseholdMaterialService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HouseholdMaterialController extends Controller implements HasMiddleware
{
    public function __construct(private readonly HouseholdMaterialService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_household_materials', only: ['index']),
            new Middleware('permission:create_household_materials', only: ['create', 'store']),
            new Middleware('permission:edit_household_materials', only: ['edit', 'update']),
            new Middleware('permission:delete_household_materials', only: ['destroy']),
        ];
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
        $this->service->store($request->validated());

        return to_route('household.materials.index')->with('success', 'Хозяйственный материал успешно добавлен!');
    }

    public function edit(HouseholdMaterial $material): View
    {
        return view('admin.household_materials.edit', compact('material'));
    }

    public function update(HouseholdMaterialRequest $request, HouseholdMaterial $material): RedirectResponse
    {
        $this->service->update($material, $request->validated());

        return to_route('household.materials.index')->with('success', 'Хозяйственный материал успешно обновлен!');
    }

    public function destroy(HouseholdMaterial $material): RedirectResponse
    {
        $this->service->destroy($material);

        return to_route('household.materials.index')->with('success', 'Хозяйственный материал успешно удален!');
    }
}
