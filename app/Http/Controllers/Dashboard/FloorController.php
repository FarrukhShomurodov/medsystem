<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\FloorRequest;
use App\Models\Floor;
use App\Services\FloorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class FloorController extends Controller
{
    protected FloorService $service;

    public function __construct(FloorService $service)
    {
        $this->middleware(['permission:list_floors'])->only('index');
        $this->middleware(['permission:view_floors'])->only('show');
        $this->middleware(['permission:create_floors'])->only(['create', 'store']);
        $this->middleware(['permission:edit_floors'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_floors'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $floors = Floor::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.floors.index', compact('floors'));
    }

    public function create(): View
    {
        return view('admin.floors.create');
    }

    public function show(Floor $floor): View
    {
        return view('admin.floors.show', compact('floor'));
    }

    public function store(FloorRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('floors.index')->with('success', 'Этаж  успешно добавлен!');
    }

    public function edit(floor $floor): View
    {
        return view('admin.floors.edit', compact('floor'));
    }

    public function update(FloorRequest $request, Floor $floor): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($floor, (array)$validated);

        return redirect()->route('floors.index')->with('success', 'Этаж  успешно обновлен!');
    }

    public function destroy(Floor $floor): RedirectResponse
    {
        $this->service->destroy($floor);

        return redirect()->route('floors.index')->with('success', 'Этаж  успешно удален!');
    }
}
