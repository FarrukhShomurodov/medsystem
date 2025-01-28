<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\InstitutionsRequest;
use App\Models\Institution;
use App\Services\InstitutionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InstitutionController
{
    private InstitutionService $service;

    public function __construct(InstitutionService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $institutions = Institution::query()
            ->select(['id', 'name', 'address', 'phone'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.institutions.index', compact('institutions'));
    }

    public function create(): View
    {
        return view('admin.institutions.create');
    }

    public function store(InstitutionsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('institutions.index')->with('success', 'Заведение успешно добавлена!');
    }

    public function edit(Institution $institution): View
    {
        return view('admin.institutions.edit', compact('institution'));
    }

    public function update(InstitutionsRequest $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($institution, (array)$validated);

        return redirect()->route('institutions.index')->with('success', 'Заведение успешно обновлена!');
    }

    public function destroy(Institution $institution): RedirectResponse
    {
        $this->service->destroy($institution);

        return redirect()->route('institutions.index')->with('success', 'Заведение успешно удалена!');
    }
}
