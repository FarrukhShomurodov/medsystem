<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Floor;
use App\Models\WardRoom;
use App\Models\Warehouse;
use App\Services\DepartmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DepartmentController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DepartmentService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_departments', only: ['index']),
            new Middleware('permission:create_departments', only: ['create', 'store']),
            new Middleware('permission:edit_departments', only: ['edit', 'update']),
            new Middleware('permission:delete_departments', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $departments = Department::query()
            ->select(['id', 'name', 'floor_id', 'warehouse_id'])
            ->with(['warehouse', 'floor'])
            ->withCount('rooms')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $wardRooms = WardRoom::query()->withCount('beds')->get();
        $warehouses = Warehouse::query()->get();
        $floors = Floor::query()->get();

        return view('admin.departments.create', compact('wardRooms', 'warehouses', 'floors'));
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('departments.index')->with('success', 'Отделение успешно добавлено!');
    }

    public function edit(Department $department): View
    {
        $department->load(['rooms', 'staffs.position', 'staffs.staff']);
        $wardRooms = WardRoom::query()->withCount('beds')->get();
        $warehouses = Warehouse::query()->get();
        $floors = Floor::query()->get();

        return view('admin.departments.edit', compact('department', 'wardRooms', 'warehouses', 'floors'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->service->update($department, $request->validated());

        return to_route('departments.index')->with('success', 'Отделение успешно обновлено!');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->service->destroy($department);

        return to_route('departments.index')->with('success', 'Отделение успешно удалено!');
    }
}
