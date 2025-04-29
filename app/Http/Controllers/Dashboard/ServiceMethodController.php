<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceMethodRequest;
use App\Models\ServiceMethod;
use App\Services\ServicMethodService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceMethodController extends Controller
{
    protected ServicMethodService $service;

    public function __construct(ServicMethodService $service)
    {
        $this->middleware(['permission:view_service_methods'])->only('index');
        $this->middleware(['permission:create_service_methods'])->only(['create', 'store']);
        $this->middleware(['permission:edit_service_methods'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_service_methods'])->only('destroy');

        $this->service = $service;
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
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('service.methods.index')->with('success', 'Метод успешно добавлен!');
    }

    public function edit(ServiceMethod $method): View
    {
        return view('admin.service_methods.edit', compact('method'));
    }

    public function update(ServiceMethodRequest $request, ServiceMethod $method): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($method, (array)$validated);

        return redirect()->route('service.methods.index')->with('success', 'Метод успешно обновлен!');
    }

    public function destroy(ServiceMethod $method): RedirectResponse
    {
        $this->service->destroy($method);

        return redirect()->route('service.methods.index')->with('success', 'Метод успешно удален!');
    }
}
