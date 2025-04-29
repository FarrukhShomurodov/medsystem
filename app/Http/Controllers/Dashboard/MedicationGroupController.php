<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationGroupRequest;
use App\Models\MedicationGroup;
use App\Services\MedicationGroupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MedicationGroupController extends Controller
{
    protected MedicationGroupService $service;

    public function __construct(MedicationGroupService $service)
    {
        $this->middleware(['permission:view_medication_groups'])->only('index');
        $this->middleware(['permission:create_medication_groups'])->only(['create', 'store']);
        $this->middleware(['permission:edit_medication_groups'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_medication_groups'])->only('destroy');

        $this->service = $service;
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
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('medication.groups.index')->with('success', 'Группа успешно добавлена!');
    }

    public function edit(MedicationGroup $group): View
    {
        return view('admin.medication_groups.edit', compact('group'));
    }

    public function update(MedicationGroupRequest $request, MedicationGroup $group): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($group, (array)$validated);

        return redirect()->route('medication.groups.index')->with('success', 'Группа успешно обновлена!');
    }

    public function destroy(MedicationGroup $group): RedirectResponse
    {
        $this->service->destroy($group);

        return redirect()->route('medication.groups.index')->with('success', 'Группа успешно удалена!');
    }
}
