<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiagnosisRequest;
use App\Http\Requests\ImportSpreadsheetRequest;
use App\Imports\DiagnosisImport;
use App\Models\Diagnosis;
use App\Models\Survey;
use App\Services\DiagnosisService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class DiagnosisController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DiagnosisService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_diagnoses', only: ['index']),
            new Middleware('permission:create_diagnoses', only: ['create', 'store']),
            new Middleware('permission:edit_diagnoses', only: ['edit', 'update']),
            new Middleware('permission:delete_diagnoses', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $diagnoses = Diagnosis::query()
            ->select(['id', 'name', 'code'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.diagnoses.index', compact('diagnoses'));
    }

    public function create(): View
    {
        return view('admin.diagnoses.create');
    }

    public function store(DiagnosisRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('diagnoses.index')->with('success', 'Диагноз успешно добавлен!');
    }

    public function edit(Diagnosis $diagnosis): View
    {
        $diagnosis->load('surveys');
        $surveys = Survey::query()->get();

        return view('admin.diagnoses.edit', compact('diagnosis', 'surveys'));
    }

    public function update(DiagnosisRequest $request, Diagnosis $diagnosis): RedirectResponse
    {
        $this->service->update($diagnosis, $request->validated());

        return to_route('diagnoses.index')->with('success', 'Диагноз успешно обновлен!');
    }

    public function destroy(Diagnosis $diagnosis): RedirectResponse
    {
        $this->service->destroy($diagnosis);

        return to_route('diagnoses.index')->with('success', 'Диагноз успешно удален!');
    }

    public function showImportDiagnosis(): View
    {
        return view('admin.diagnoses.import');
    }

    public function importDiagnosis(ImportSpreadsheetRequest $request): RedirectResponse
    {
        $import = new DiagnosisImport;
        Excel::import($import, $request->file('file'));

        return $import->report()->flashTo(to_route('diagnoses.index'));
    }
}
