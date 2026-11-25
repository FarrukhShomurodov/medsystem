<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegionRequest;
use App\Models\Region;
use App\Services\RegionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RegionController extends Controller implements HasMiddleware
{
    public function __construct(private readonly RegionService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_regions', only: ['index']),
            new Middleware('permission:create_regions', only: ['create', 'store']),
            new Middleware('permission:edit_regions', only: ['edit', 'update']),
            new Middleware('permission:delete_regions', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $regions = Region::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.regions.index', compact('regions'));
    }

    public function create(): View
    {
        return view('admin.regions.create');
    }

    public function store(RegionRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('regions.index')->with('success', 'Область успешно добавлена!');
    }

    public function edit(Region $region): View
    {
        return view('admin.regions.edit', compact('region'));
    }

    public function update(RegionRequest $request, Region $region): RedirectResponse
    {
        $this->service->update($region, $request->validated());

        return to_route('regions.index')->with('success', 'Область успешно обновлена!');
    }

    public function destroy(Region $region): RedirectResponse
    {
        $this->service->destroy($region);

        return to_route('regions.index')->with('success', 'Область успешно удалена!');
    }
}
