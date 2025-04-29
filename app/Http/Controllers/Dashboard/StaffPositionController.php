<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffPositionRequest;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;

class StaffPositionController extends Controller
{
    protected StaffPositionService $service;

    public function __construct(StaffPositionService $service)
    {
        $this->middleware(['permission:view_staff_positions'])->only('index');
        $this->middleware(['permission:create_staff_positions'])->only(['create', 'store']);
        $this->middleware(['permission:edit_staff_positions'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_staff_positions'])->only('destroy');

        $this->service = $service;
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
        $permissions = Permission::query()->get();

        return view('admin.staff_positions.create', compact('permissions'));
    }

    public function store(StaffPositionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно добавлен!');
    }

    public function edit(StaffPosition $position): View
    {
        $permissions = Permission::query()->get();

        return view('admin.staff_positions.edit', compact('position', 'permissions'));
    }

    public function update(StaffPositionRequest $request, StaffPosition $position): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($position, (array)$validated);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно обновлен!');
    }

    public function destroy(StaffPosition $position): RedirectResponse
    {
        if (collect([1, 2, 3, 4])->contains($position->id)) {
            return redirect()->back()->withErrors('Удаление этой должности недоступно!');
        }

        $this->service->destroy($position);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно удален!');
    }
}
