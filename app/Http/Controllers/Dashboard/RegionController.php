<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegionRequest;
use App\Models\Region;
use App\Services\RegionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RegionController extends Controller
{
    protected RegionService $service;

    public function __construct(RegionService $service)
    {
        $this->middleware(['permission:view_regions'])->only('index');
        $this->middleware(['permission:create_regions'])->only(['create', 'store']);
        $this->middleware(['permission:edit_regions'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_regions'])->only('destroy');

        $this->service = $service;
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
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('regions.index')->with('success', 'Область успешно добавлена!');
    }

    public function edit(Region $region): View
    {
        return view('admin.regions.edit', compact('region'));
    }

    public function update(RegionRequest $request, Region $region): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($region, (array)$validated);

        return redirect()->route('regions.index')->with('success', 'Область успешно обновлена!');
    }

    public function destroy(Region $region): RedirectResponse
    {
        $this->service->destroy($region);

        return redirect()->route('regions.index')->with('success', 'Область успешно удалена!');
    }
}
