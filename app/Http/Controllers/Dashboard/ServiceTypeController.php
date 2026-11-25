<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceTypeRequest;
use App\Models\ServiceType;
use App\Services\ServiceTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ServiceTypeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ServiceTypeService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_service_types', only: ['index']),
            new Middleware('permission:create_service_types', only: ['create', 'store']),
            new Middleware('permission:edit_service_types', only: ['edit', 'update']),
            new Middleware('permission:delete_service_types', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $types = ServiceType::query()
            ->select(['id', 'name'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.service_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.service_types.create');
    }

    public function store(ServiceTypeRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('service.types.index')->with('success', 'Тип услуги успешно добавлен!');
    }

    public function edit(ServiceType $type): View|RedirectResponse
    {
        if ($type->isSystem()) {
            return to_route('service.types.index')->with('error', 'Этот тип услуг системный: его нельзя изменить.');
        }

        return view('admin.service_types.edit', compact('type'));
    }

    public function update(ServiceTypeRequest $request, ServiceType $type): RedirectResponse
    {
        if ($type->isSystem()) {
            return to_route('service.types.index')->with('error', 'Этот тип услуг системный: его нельзя изменить.');
        }

        $this->service->update($type, $request->validated());

        return to_route('service.types.index')->with('success', 'Тип услуги успешно обновлен!');
    }

    public function destroy(ServiceType $type): RedirectResponse
    {
        if ($type->isSystem()) {
            return to_route('service.types.index')->with('error', 'Этот тип услуг системный: его нельзя удалить.');
        }

        $this->service->destroy($type);

        return to_route('service.types.index')->with('success', 'Тип услуги успешно удален!');
    }
}
