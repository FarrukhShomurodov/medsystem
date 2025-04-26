<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\WardRoomRequest;
use App\Models\WardRoom;
use App\Services\WardRoomService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WardRoomController
{
    protected WardRoomService $service;

    public function __construct(WardRoomService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $rooms = WardRoom::query()
            ->select(['id', 'room_number'])
            ->with('beds')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.department_rooms.index', compact('rooms'));
    }

    public function show(WardRoom $room): View
    {
        return view('admin.department_rooms.show', compact('room'));
    }

    public function create(): View
    {
        return view('admin.department_rooms.create');
    }

    public function store(WardRoomRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('departments.room.index')->with('success', 'Палата успешно добавлена!');
    }

    public function edit(WardRoom $room): View
    {
        return view('admin.department_rooms.edit', compact('room'));
    }

    public function update(WardRoomRequest $request, WardRoom $room): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($room, (array)$validated);

        return redirect()->route('departments.room.index')->with('success', 'Палата успешно обновлена!');
    }

    public function destroy(WardRoom $room): RedirectResponse
    {
        $this->service->destroy($room);

        return redirect()->route('departments.room.index')->with('success', 'Палата успешно удалена!');
    }
}
