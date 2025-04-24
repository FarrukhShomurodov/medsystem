<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceController
{
    protected ServiceService $service;

    public function __construct(ServiceService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $services = Service::query()
            ->select(['id', 'name', 'price', 'discount', 'service_category_id', 'is_active'])
            ->with('category')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        $categories = ServiceCategory::query()->get();

        return view('admin.services.create', compact('categories'));
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('services.index')->with('success', 'Услуга успешно добавлена!');
    }

    public function edit(Service $service): View
    {
        $categories = ServiceCategory::query()->get();

        return view('admin.services.edit', compact('categories', 'service'));
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($service, (array)$validated);

        return redirect()->route('services.index')->with('success', 'Услуга  успешно обновлена!');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->service->destroy($service);

        return redirect()->route('services.index')->with('success', 'Услуга успешно удалена!');
    }
}
