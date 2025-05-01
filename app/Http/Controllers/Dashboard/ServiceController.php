<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Services\ServiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceController extends Controller
{
    protected ServiceService $service;

    public function __construct(ServiceService $service)
    {
        $this->middleware(['permission:view_services'])->only('index');
        $this->middleware(['permission:create_services'])->only(['create', 'store']);
        $this->middleware(['permission:edit_services'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_services'])->only('destroy');


        $this->service = $service;
    }

    public function index(): View
    {
        $services = Service::query()
            ->select(['id', 'name', 'price', 'discount', 'service_category_id', 'is_active', 'service_type_id'])
            ->with('category')
            ->with('type')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        $categories = ServiceCategory::query()->get();
        $types = ServiceType::query()->get();

        return view('admin.services.create', compact('categories', 'types'));
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
        $types = ServiceType::query()->get();

        return view('admin.services.edit', compact('categories', 'service', 'types'));
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
