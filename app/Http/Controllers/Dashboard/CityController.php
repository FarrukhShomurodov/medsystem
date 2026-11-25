<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CityRequest;
use App\Models\City;
use App\Models\Region;
use App\Services\CityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CityController extends Controller implements HasMiddleware
{
    public function __construct(private readonly CityService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_cities', only: ['index']),
            new Middleware('permission:create_cities', only: ['create', 'store']),
            new Middleware('permission:edit_cities', only: ['edit', 'update']),
            new Middleware('permission:delete_cities', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $cities = City::query()
            ->select(['id', 'name', 'region_id'])
            ->with('region')
            ->orderByDesc('id')
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
        $this->service->store($request->city());

        return to_route('cities.index')->with('success', 'Город успешно добавлен!');
    }

    public function edit(City $city): View
    {
        $regions = Region::query()->get();

        return view('admin.cities.edit', compact('regions', 'city'));
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $this->service->update($city, $request->city());

        return to_route('cities.index')->with('success', 'Город успешно обновлен!');
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->service->destroy($city);

        return to_route('cities.index')->with('success', 'Город успешно удален!');
    }
}
