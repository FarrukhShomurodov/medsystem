<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\AnalyzeRequest;
use App\Models\Analyze;
use App\Models\AnalyzeMethod;
use App\Models\AnalyzeType;
use App\Models\Doctor;
use App\Models\ExaminationMap;
use App\Models\Laboratory;
use App\Models\MedicalEquipment;
use App\Models\Patient;
use App\Services\AnalyzeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AnalyzeController
{
    protected AnalyzeService $service;

    public function __construct(AnalyzeService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $analyzes = Analyze::query()
            ->select(['id', 'patient_id', 'status'])
            ->with('patient')
            ->with('types')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.analyzes.index', compact('analyzes'));
    }

    public function create(): View
    {
        $types = AnalyzeType::query()->get();
        $doctors = Doctor::query()->get();
        $laboratories = Laboratory::query()->get();
        $equipments = MedicalEquipment::query()->get();
        $methods = AnalyzeMethod::query()->get();
        $patients = Patient::query()->get();
        $maps = ExaminationMap::query()->get();

        return view('admin.analyzes.create', compact('doctors', 'types', 'patients', 'laboratories', 'equipments', 'maps', 'methods'));
    }

    public function store(AnalyzeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('analyzes.index')->with('success', 'Анализ успешно добавлен!');
    }

    public function edit(Analyze $analyze): View
    {
        $types = AnalyzeType::query()->get();
        $doctors = Doctor::query()->get();
        $laboratories = Laboratory::query()->get();
        $equipments = MedicalEquipment::query()->get();
        $methods = AnalyzeMethod::query()->get();
        $patients = Patient::query()->get();
        $maps = ExaminationMap::query()->get();

        return view('admin.analyzes.edit', compact('doctors', 'types', 'patients', 'laboratories', 'equipments', 'maps', 'methods', 'analyze'));
    }

    public function update(AnalyzeRequest $request, Analyze $analyze): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($analyze, (array)$validated);

        return redirect()->route('analyzes.index')->with('success', 'Анализ  успешно обновлен!');
    }

    public function destroy(Analyze $analyze): RedirectResponse
    {
        $this->service->destroy($analyze);

        return redirect()->route('analyzes.index')->with('success', 'Анализ успешно удален!');
    }
}
