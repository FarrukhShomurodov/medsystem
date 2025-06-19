<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\ServiceImportValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Imports\ServicesImport;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceType;
use App\Services\ServiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            ->select(
                [
                    'id',
                    'name',
                    'price',
                    'discount',
                    'service_group_id',
                    'is_active',
                    'service_type_id',
                    'is_appointment'
                ]
            )
            ->with('group')
            ->with('type')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        $groups = ServiceGroup::query()->get();
        $types = ServiceType::query()->get();

        return view('admin.services.create', compact('groups', 'types'));
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('services.index')->with('success', 'Услуга успешно добавлена!');
    }

    public function edit(Service $service): View
    {
        $groups = ServiceGroup::query()->get();
        $types = ServiceType::query()->get();

        return view('admin.services.edit', compact('groups', 'service', 'types'));
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

    public function showImportServices(): View
    {
        return view('admin.services.import');
    }

    public function importServices(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new ServicesImport, $request->file('file'));
        } catch (ServiceImportValidationException $e) {
            return back()->withErrors(['import_error' => $e->getMessage()]);
        }

        return redirect()->route('services.index')->with('success', 'Услуги успешно импортированы.');
    }
}
