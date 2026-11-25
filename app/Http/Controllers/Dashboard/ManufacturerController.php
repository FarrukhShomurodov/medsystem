<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManufacturerRequest;
use App\Models\Manufacturer;
use App\Services\ManufacturerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ManufacturerController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ManufacturerService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_manufacturers', only: ['index']),
            new Middleware('permission:create_manufacturers', only: ['create', 'store']),
            new Middleware('permission:edit_manufacturers', only: ['edit', 'update']),
            new Middleware('permission:delete_manufacturers', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $manufacturers = Manufacturer::query()
            ->select(['id', 'name', 'country_of_manufacture'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.manufacturers.index', compact('manufacturers'));
    }

    public function create(): View
    {
        return view('admin.manufacturers.create');
    }

    public function store(ManufacturerRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('manufacturers.index')->with('success', 'Производитель успешно добавлен!');
    }

    public function edit(Manufacturer $manufacturer): View
    {
        return view('admin.manufacturers.edit', compact('manufacturer'));
    }

    public function update(ManufacturerRequest $request, Manufacturer $manufacturer): RedirectResponse
    {
        $this->service->update($manufacturer, $request->validated());

        return to_route('manufacturers.index')->with('success', 'Производитель успешно обновлен!');
    }

    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        $this->service->destroy($manufacturer);

        return to_route('manufacturers.index')->with('success', 'Производитель успешно удален!');
    }
}
