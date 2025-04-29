<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\StaffPositionRequest;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;

class StaffPositionController
{
    protected StaffPositionService $service;

    public function __construct(StaffPositionService $service)
    {
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
        $this->service->destroy($position);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно удален!');
    }
}
