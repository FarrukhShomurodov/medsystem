<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceMethodRequest;
use App\Models\ServiceMethod;
use App\Services\ServiceMethodService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ServiceMethodController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ServiceMethodService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_service_methods', only: ['index']),
            new Middleware('permission:create_service_methods', only: ['create', 'store']),
            new Middleware('permission:edit_service_methods', only: ['edit', 'update']),
            new Middleware('permission:delete_service_methods', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $methods = ServiceMethod::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.service_methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('admin.service_methods.create');
    }

    public function store(ServiceMethodRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('service.methods.index')->with('success', 'Метод успешно добавлен!');
    }

    public function edit(ServiceMethod $method): View
    {
        return view('admin.service_methods.edit', compact('method'));
    }

    public function update(ServiceMethodRequest $request, ServiceMethod $method): RedirectResponse
    {
        $this->service->update($method, $request->validated());

        return to_route('service.methods.index')->with('success', 'Метод успешно обновлен!');
    }

    public function destroy(ServiceMethod $method): RedirectResponse
    {
        $this->service->destroy($method);

        return to_route('service.methods.index')->with('success', 'Метод успешно удален!');
    }
}
