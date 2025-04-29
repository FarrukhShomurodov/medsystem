<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentTypeRequest;
use App\Models\DepartmentType;
use App\Services\DepartmentTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DepartmentTypeController extends Controller
{
    protected DepartmentTypeService $service;

    public function __construct(DepartmentTypeService $service)
    {
        $this->middleware(['permission:view_department_types'])->only('index');
        $this->middleware(['permission:create_department_types'])->only(['create', 'store']);
        $this->middleware(['permission:edit_department_types'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_department_types'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $types = DepartmentType::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.department_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.department_types.create');
    }

    public function store(DepartmentTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('departments.type.index')->with('success', 'Тип отделение успешно добавлен!');
    }

    public function edit(DepartmentType $type): View
    {
        return view('admin.department_types.edit', compact('type'));
    }

    public function update(DepartmentTypeRequest $request, DepartmentType $type): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($type, (array)$validated);

        return redirect()->route('departments.type.index')->with('success', 'Тип отделение успешно обновлен!');
    }

    public function destroy(DepartmentType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return redirect()->route('departments.type.index')->with('success', 'Тип отделение успешно удален!');
    }
}
