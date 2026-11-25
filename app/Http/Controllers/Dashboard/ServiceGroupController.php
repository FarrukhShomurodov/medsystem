<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceGroupRequest;
use App\Models\ServiceGroup;
use App\Services\ServiceGroupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ServiceGroupController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ServiceGroupService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_service_groups', only: ['index']),
            new Middleware('permission:create_service_groups', only: ['create', 'store']),
            new Middleware('permission:edit_service_groups', only: ['edit', 'update']),
            new Middleware('permission:delete_service_groups', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $groups = ServiceGroup::query()
            ->select(['id', 'name'])
            ->with('subGroups')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.service_groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('admin.service_groups.create');
    }

    public function store(ServiceGroupRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('service.groups.index')->with('success', 'Группа успешно добавлена!');
    }

    public function edit(ServiceGroup $group): View
    {
        $group->load('subGroups');

        return view('admin.service_groups.edit', compact('group'));
    }

    public function update(ServiceGroupRequest $request, ServiceGroup $group): RedirectResponse
    {
        $this->service->update($group, $request->validated());

        return to_route('service.groups.index')->with('success', 'Группа успешно обновлена!');
    }

    public function destroy(ServiceGroup $group): RedirectResponse
    {
        $this->service->destroy($group);

        return to_route('service.groups.index')->with('success', 'Группа успешно удалена!');
    }
}
