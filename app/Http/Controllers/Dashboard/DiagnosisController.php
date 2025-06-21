<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiagnosisRequest;
use App\Models\Diagnosis;
use App\Services\DiagnosisService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DiagnosisController extends Controller
{
    protected DiagnosisService $service;

    public function __construct(DiagnosisService $service)
    {
        $this->middleware(['permission:view_diagnoses'])->only('index');
        $this->middleware(['permission:create_diagnoses'])->only(['create', 'store']);
        $this->middleware(['permission:edit_diagnoses'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_diagnoses'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $diagnoses = Diagnosis::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.diagnoses.index', compact('diagnoses'));
    }

    public function create(): View
    {
        return view('admin.diagnoses.create',);
    }

    public function store(DiagnosisRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('diagnoses.index')->with('success', 'Диагноз успешно добавлен!');
    }

    public function edit(Diagnosis $diagnosis): View
    {
        return view('admin.diagnoses.edit', compact('diagnosis'));
    }

    public function update(diagnosisRequest $request, Diagnosis $diagnosis): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($diagnosis, (array)$validated);

        return redirect()->route('diagnoses.index')->with('success', 'Диагноз  успешно обновлен!');
    }

    public function destroy(Diagnosis $diagnosis): RedirectResponse
    {
        $this->service->destroy($diagnosis);

        return redirect()->route('diagnoses.index')->with('success', 'Диагноз успешно удален!');
    }
}
