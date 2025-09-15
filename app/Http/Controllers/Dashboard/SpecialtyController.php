<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialtyRequest;
use App\Models\Specialty;
use App\Services\SpecialtyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SpecialtyController extends Controller
{
    protected SpecialtyService $service;

    public function __construct(SpecialtyService $service)
    {
        $this->middleware(['permission:list_specialties'])->only('index');
        $this->middleware(['permission:create_specialties'])->only(['create', 'store']);
        $this->middleware(['permission:edit_specialties'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_specialties'])->only('destroy');

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
