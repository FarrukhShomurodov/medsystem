<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\ServiceImportValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiagnosisRequest;
use App\Imports\DiagnosisImport;
use App\Models\Diagnosis;
use App\Models\Survey;
use App\Services\DiagnosisService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            ->select(['id', 'name', 'code'])
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
        $surveys = Survey::query()->get();
        return view('admin.diagnoses.edit', compact('diagnosis', 'surveys'));
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


    public function showImportDiagnosis(): View
    {
        return view('admin.diagnoses.import');
    }

    public function importDiagnosis(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new DiagnosisImport(), $request->file('file'));
        } catch (ServiceImportValidationException $e) {
            return back()->withErrors(['import_error' => $e->getMessage()]);
        }

        return redirect()->route('diagnoses.index')->with('success', 'Успешно импортированы.');
    }
}
