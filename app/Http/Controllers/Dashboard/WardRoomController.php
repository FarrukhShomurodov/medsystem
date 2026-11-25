<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\WardRoomRequest;
use App\Models\WardRoom;
use App\Services\WardRoomService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WardRoomController extends Controller implements HasMiddleware
{
    public function __construct(private readonly WardRoomService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_department_rooms', only: ['index']),
            new Middleware('permission:create_department_rooms', only: ['create', 'store']),
            new Middleware('permission:edit_department_rooms', only: ['edit', 'update']),
            new Middleware('permission:delete_department_rooms', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $rooms = WardRoom::query()
            ->select(['id', 'room_number'])
            ->withCount('beds')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.department_rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        return view('admin.department_rooms.create');
    }

    public function store(WardRoomRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('departments.room.index')->with('success', 'Палата успешно добавлена!');
    }

    public function edit(WardRoom $room): View
    {
        $room->load('beds');

        return view('admin.department_rooms.edit', compact('room'));
    }

    public function update(WardRoomRequest $request, WardRoom $room): RedirectResponse
    {
        $this->service->update($room, $request->validated());

        return to_route('departments.room.index')->with('success', 'Палата успешно обновлена!');
    }

    public function destroy(WardRoom $room): RedirectResponse
    {
        $this->service->destroy($room);

        return to_route('departments.room.index')->with('success', 'Палата успешно удалена!');
    }
}
