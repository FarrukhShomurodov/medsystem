<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceGroupRequest;
use App\Models\ServiceGroup;
use App\Services\ServiceGroupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceGroupController extends Controller
{
    protected ServiceGroupService $service;

    public function __construct(ServiceGroupService $service)
    {
        $this->middleware(['permission:view_service_groups'])->only('index');
        $this->middleware(['permission:create_service_groups'])->only(['create', 'store']);
        $this->middleware(['permission:edit_service_groups'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_service_groups'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $groups = ServiceGroup::query()
            ->select(['id', 'name'])
            ->with('subGroups')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.service_groups.index', compact('groups'));
    }

    public function show(ServiceGroup $group): View
    {
        return view('admin.service_groups.show', compact('group'));
    }

    public function create(): View
    {
        return view('admin.service_groups.create');
    }

    public function store(ServiceGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('service.groups.index')->with('success', 'Группа успешно добавлена!');
    }

    public function edit(ServiceGroup $group): View
    {
        return view('admin.service_groups.edit', compact('group'));
    }

    public function update(ServiceGroupRequest $request, ServiceGroup $group): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($group, (array)$validated);

        return redirect()->route('service.groups.index')->with('success', 'Группа успешно обновлена!');
    }

    public function destroy(ServiceGroup $group): RedirectResponse
    {
        $this->service->destroy($group);

        return redirect()->route('service.groups.index')->with('success', 'Группа успешно удалена!');
    }
}
