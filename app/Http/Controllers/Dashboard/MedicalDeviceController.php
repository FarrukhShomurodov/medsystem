<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\MedicalDeviceRequest;
use App\Models\MedicalDevice;
use App\Services\MedicalDeviceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MedicalDeviceController
{
    protected MedicalDeviceService $service;

    public function __construct(MedicalDeviceService $service)
    {
        $this->service = $service;
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
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('devices.index')->with('success', 'Медицинское устройство успешно добавлена!');
    }

    public function edit(MedicalDevice $device): View
    {
        return view('admin.devices.edit', compact('device'));
    }

    public function update(MedicalDeviceRequest $request, MedicalDevice $device): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($device, (array)$validated);

        return redirect()->route('devices.index')->with('success', 'Медицинское устройство  успешно обновлена!');
    }

    public function destroy(MedicalDevice $device): RedirectResponse
    {
        $this->service->destroy($device);

        return redirect()->route('devices.index')->with('success', 'Медицинское устройство успешно удалена!');
    }
}
