<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationGroupRequest;
use App\Models\MedicationGroup;
use App\Services\MedicationGroupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MedicationGroupController extends Controller implements HasMiddleware
{
    public function __construct(private readonly MedicationGroupService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_medication_groups', only: ['index']),
            new Middleware('permission:create_medication_groups', only: ['create', 'store']),
            new Middleware('permission:edit_medication_groups', only: ['edit', 'update']),
            new Middleware('permission:delete_medication_groups', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $groups = MedicationGroup::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.medication_groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('admin.medication_groups.create');
    }

    public function store(MedicationGroupRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('medication.groups.index')->with('success', 'Группа успешно добавлена!');
    }

    public function edit(MedicationGroup $group): View
    {
        return view('admin.medication_groups.edit', compact('group'));
    }

    public function update(MedicationGroupRequest $request, MedicationGroup $group): RedirectResponse
    {
        $this->service->update($group, $request->validated());

        return to_route('medication.groups.index')->with('success', 'Группа успешно обновлена!');
    }

    public function destroy(MedicationGroup $group): RedirectResponse
    {
        $this->service->destroy($group);

        return to_route('medication.groups.index')->with('success', 'Группа успешно удалена!');
    }
}
