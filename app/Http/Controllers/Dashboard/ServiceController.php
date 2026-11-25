<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportSpreadsheetRequest;
use App\Http\Requests\ServiceRequest;
use App\Imports\ServicesImport;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceType;
use App\Services\ServiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class ServiceController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ServiceService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_services', only: ['index']),
            new Middleware('permission:create_services', only: ['create', 'store']),
            new Middleware('permission:edit_services', only: ['edit', 'update']),
            new Middleware('permission:delete_services', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $services = Service::query()
            ->select(['id', 'name', 'price', 'discount', 'service_group_id', 'is_active', 'service_type_id', 'is_appointment'])
            ->with(['group', 'type'])
            ->orderByDesc('id')
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
        $this->service->store($request->validated());

        return to_route('services.index')->with('success', 'Услуга успешно добавлена!');
    }

    public function edit(Service $service): View
    {
        $groups = ServiceGroup::query()->get();
        $types = ServiceType::query()->get();

        return view('admin.services.edit', compact('groups', 'service', 'types'));
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->service->update($service, $request->validated());

        return to_route('services.index')->with('success', 'Услуга успешно обновлена!');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->service->destroy($service);

        return to_route('services.index')->with('success', 'Услуга успешно удалена!');
    }

    public function showImportServices(): View
    {
        return view('admin.services.import');
    }

    public function importServices(ImportSpreadsheetRequest $request): RedirectResponse
    {
        $import = new ServicesImport;
        Excel::import($import, $request->file('file'));

        return $import->report()->flashTo(to_route('services.index'));
    }
}
