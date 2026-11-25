<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffPositionRequest;
use App\Models\Admin;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;

class StaffPositionController extends Controller implements HasMiddleware
{
    public function __construct(private readonly StaffPositionService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_staff_positions', only: ['index']),
            new Middleware('permission:create_staff_positions', only: ['create', 'store']),
            new Middleware('permission:edit_staff_positions', only: ['edit', 'update']),
            new Middleware('permission:delete_staff_positions', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $positions = StaffPosition::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.staff_positions.index', compact('positions'));
    }

    public function create(): View
    {
        return view('admin.staff_positions.create', [
            'groupedPermissions' => $this->groupedPermissions(),
        ]);
    }

    public function edit(StaffPosition $position): View
    {
        return view('admin.staff_positions.edit', [
            'position' => $position,
            'groupedPermissions' => $this->groupedPermissions(),
            'selectedPermissions' => $this->service->roleOf($position)->permissions->pluck('name')->all(),
        ]);
    }

    public function store(StaffPositionRequest $request): RedirectResponse
    {
        $this->service->store($request->position());

        return to_route('staffs.position.index')->with('success', 'Должность успешно добавлена!');
    }

    public function update(StaffPositionRequest $request, StaffPosition $position): RedirectResponse
    {
        $this->service->update($position, $request->position());

        return to_route('staffs.position.index')->with('success', 'Должность успешно обновлена!');
    }

    public function destroy(StaffPosition $position): RedirectResponse
    {
        if ($position->isSeeded()) {
            return back()->with('error', 'Удаление этой должности недоступно!');
        }

        $this->service->destroy($position);

        return to_route('staffs.position.index')->with('success', 'Должность успешно удалена!');
    }

    /**
     * The permissions of the admin guard for the checkboxes of the form, grouped by the action
     * their name starts with.
     *
     * @return array<string, list<array{id: int|string, name: string, russian_name: string}>>
     */
    private function groupedPermissions(): array
    {
        $grouped = [];

        foreach (Permission::query()->where('guard_name', Admin::GUARD)->orderBy('id')->get() as $permission) {
            $category = match (true) {
                str_starts_with($permission->name, 'view_') => 'Просмотр',
                str_starts_with($permission->name, 'create_') => 'Создание',
                str_starts_with($permission->name, 'edit_') => 'Редактирование',
                str_starts_with($permission->name, 'delete_') => 'Удаление',
                default => 'Общие',
            };

            $grouped[$category][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'russian_name' => PermissionCatalog::label($permission->name),
            ];
        }

        return $grouped;
    }
}
