<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManufacturerRequest;
use App\Models\Manufacturer;
use App\Services\ManufactureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ManufacturerController extends Controller
{
    protected ManufactureService $service;

    public function __construct(ManufactureService $service)
    {
        $this->middleware(['permission:view_manufacturers'])->only('index');
        $this->middleware(['permission:create_manufacturers'])->only(['create', 'store']);
        $this->middleware(['permission:edit_manufacturers'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_manufacturers'])->only('destroy');

        $this->service = $service;
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
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('manufacturers.index')->with('success', 'Производитель успешно добавлен!');
    }

    public function edit(Manufacturer $manufacturer): View
    {
        return view('admin.manufacturers.edit', compact('manufacturer'));
    }

    public function update(ManufacturerRequest $request, Manufacturer $manufacturer): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($manufacturer, (array)$validated);

        return redirect()->route('manufacturers.index')->with('success', 'Производитель успешно обновлен!');
    }

    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        $this->service->destroy($manufacturer);

        return redirect()->route('manufacturers.index')->with('success', 'Производитель успешно удален!');
    }
}
