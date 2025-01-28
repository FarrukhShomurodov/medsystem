<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\DoctorRequest;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Services\DoctorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DoctorController
{
    private DoctorService $service;

    public function __construct(DoctorService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $doctors = Doctor::query()
            ->select(['id', 'first_name', 'last_name', 'middle_name', 'specialty_id'])
            ->with('specialty')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        $specialties = Specialty::query()->get(['id', 'name']);
        return view('admin.doctors.create', compact('specialties'));
    }

    public function store(DoctorRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('doctors.index')->with('success', 'Врач успешно добавлен!');
    }

    public function edit(Doctor $doctor): View
    {
        $specialties = Specialty::query()->get(['id', 'name']);
        return view('admin.doctors.edit', compact('doctor', 'specialties'));
    }

    public function update(DoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($doctor, (array)$validated);

        return redirect()->route('doctors.index')->with('success', 'Врач успешно обновлен!');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->service->destroy($doctor);

        return redirect()->route('doctors.index')->with('success', 'Врач успешно удален!');
    }
}
