<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CityRequest;
use App\Models\City;
use App\Models\Region;
use App\Services\CityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CityController extends Controller
{
    protected CityService $service;

    public function __construct(CityService $service)
    {
        $this->middleware(['permission:list_cities'])->only('index');
        $this->middleware(['permission:create_cities'])->only(['create', 'store']);
        $this->middleware(['permission:edit_cities'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_cities'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $cities = City::query()
            ->select(['id', 'name', 'region_id'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.cities.index', compact('cities'));
    }

    public function create(): View
    {
        $regions = Region::query()->get();
        return view('admin.cities.create', compact('regions'));
    }

    public function store(CityRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('cities.index')->with('success', 'Город успешно добавлен!');
    }

    public function edit(City $city): View
    {
        $regions = Region::query()->get();
        return view('admin.cities.edit', compact('regions', 'city'));
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($city, (array)$validated);

        return redirect()->route('cities.index')->with('success', 'Город успешно обновлен!');
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->service->destroy($city);

        return redirect()->route('cities.index')->with('success', 'Город успешно удален!');
    }
}
