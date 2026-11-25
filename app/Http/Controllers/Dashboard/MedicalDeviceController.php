<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalDeviceRequest;
use App\Models\MedicalDevice;
use App\Services\MedicalDeviceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MedicalDeviceController extends Controller implements HasMiddleware
{
    public function __construct(private readonly MedicalDeviceService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_devices', only: ['index']),
            new Middleware('permission:create_devices', only: ['create', 'store']),
            new Middleware('permission:edit_devices', only: ['edit', 'update']),
            new Middleware('permission:delete_devices', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $devices = MedicalDevice::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.devices.index', compact('devices'));
    }

    public function create(): View
    {
        return view('admin.devices.create');
    }

    public function store(MedicalDeviceRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('devices.index')->with('success', 'Медицинское устройство успешно добавлено!');
    }

    public function edit(MedicalDevice $device): View
    {
        return view('admin.devices.edit', compact('device'));
    }

    public function update(MedicalDeviceRequest $request, MedicalDevice $device): RedirectResponse
    {
        $this->service->update($device, $request->validated());

        return to_route('devices.index')->with('success', 'Медицинское устройство успешно обновлено!');
    }

    public function destroy(MedicalDevice $device): RedirectResponse
    {
        $this->service->destroy($device);

        return to_route('devices.index')->with('success', 'Медицинское устройство успешно удалено!');
    }
}
