<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceTypeRequest;
use App\Models\ServiceType;
use App\Services\ServiceTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceTypeController extends Controller
{
    protected ServiceTypeService $service;

    public function __construct(ServiceTypeService $service)
    {
        $this->middleware(['permission:view_service_types'])->only('index');
        $this->middleware(['permission:create_service_types'])->only(['create', 'store']);
        $this->middleware(['permission:edit_service_types'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_service_types'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $types = ServiceType::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.service_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.service_types.create');
    }

    public function store(ServiceTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('service.types.index')->with('success', 'Тип успешно добавлена!');
    }

    public function edit(ServiceType $type)
    {
        if ($type->id == 1) {
            return redirect()->back()->withErrors('Редактирования этого типа недоступно!');
        }

        return view('admin.service_types.edit', compact('type'));
    }

    public function update(ServiceTypeRequest $request, ServiceType $type): RedirectResponse
    {
        $validated = $request->validated();

        if ($type->id == 1) {
            return redirect()->back()->withErrors('Редактирования этого типа недоступно!');
        }

        $this->service->update($type, (array)$validated);

        return redirect()->route('service.types.index')->with('success', 'Тип успешно обновлена!');
    }

    public function destroy(ServiceType $type): RedirectResponse
    {
        if ($type->id == 1) {
            return redirect()->back()->withErrors('Удаление этого типа недоступно!');
        }

        $this->service->destroy($type);

        return redirect()->route('service.types.index')->with('success', 'Тип успешно удалена!');
    }
}
