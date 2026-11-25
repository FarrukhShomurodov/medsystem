<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\FloorRequest;
use App\Models\Floor;
use App\Services\FloorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FloorController extends Controller implements HasMiddleware
{
    public function __construct(private readonly FloorService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_floors', only: ['index']),
            new Middleware('permission:view_floors', only: ['show']),
            new Middleware('permission:create_floors', only: ['create', 'store']),
            new Middleware('permission:edit_floors', only: ['edit', 'update']),
            new Middleware('permission:delete_floors', only: ['destroy']),
        ];
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
        $floor->load(['departments.warehouse', 'offices']);

        return view('admin.floors.show', compact('floor'));
    }

    public function store(FloorRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('floors.index')->with('success', 'Этаж успешно добавлен!');
    }

    public function edit(Floor $floor): View
    {
        return view('admin.floors.edit', compact('floor'));
    }

    public function update(FloorRequest $request, Floor $floor): RedirectResponse
    {
        $this->service->update($floor, $request->validated());

        return to_route('floors.index')->with('success', 'Этаж успешно обновлен!');
    }

    public function destroy(Floor $floor): RedirectResponse
    {
        $this->service->destroy($floor);

        return to_route('floors.index')->with('success', 'Этаж успешно удален!');
    }
}
