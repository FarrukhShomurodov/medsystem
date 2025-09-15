<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\WardRoom;
use App\Models\Warehouse;
use App\Services\DepartmentService;
use App\Traits\HandlesStaffAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DepartmentController extends Controller
{
    use HandlesStaffAvailability;

    protected DepartmentService $service;

    public function __construct(DepartmentService $service)
    {
        $this->middleware(['permission:list_departments'])->only('index');
        $this->middleware(['permission:create_departments'])->only(['create', 'store']);
        $this->middleware(['permission:edit_departments'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_departments'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $departments = Department::query()
            ->select(['id', 'name', 'floor', 'warehouse_id'])
            ->with('warehouse')
            ->with('rooms')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $wardRooms = WardRoom::query()->get();
        $warehouses = Warehouse::query()->get();

        return view('admin.departments.create', compact('wardRooms', 'warehouses'));
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('departments.index')->with('success', 'Отделение успешно добавлен!');
    }

    public function edit(Department $department): View
    {
        $wardRooms = WardRoom::query()->get();
        $warehouses = Warehouse::query()->get();

        $department->with('staffs');

        return view('admin.departments.edit', compact('department', 'wardRooms', 'warehouses'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($department, (array)$validated);

        return redirect()->route('departments.index')->with('success', 'Отделение успешно обновлен!');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->service->destroy($department);

        return redirect()->route('departments.index')->with('success', 'Отделение успешно удален!');
    }
}
