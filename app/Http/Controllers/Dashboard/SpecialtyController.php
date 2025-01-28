<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\SpecialtyRequest;
use App\Models\Specialty;
use App\Services\SpecialtyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SpecialtyController
{
    protected SpecialtyService $service;

    public function __construct(SpecialtyService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $specialties = Specialty::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.specialties.index', compact('specialties'));
    }

    public function create(): View
    {
        return view('admin.specialties.create');
    }

    public function store(SpecialtyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('specialties.index')->with('success', 'Специальность успешно добавлена!');
    }

    public function edit(Specialty $specialty): View
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(SpecialtyRequest $request, Specialty $specialty): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($specialty, (array)$validated);

        return redirect()->route('specialties.index')->with('success', 'Специальность успешно обновлена!');
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        $this->service->destroy($specialty);

        return redirect()->route('specialties.index')->with('success', 'Специальность успешно удалена!');
    }
}
