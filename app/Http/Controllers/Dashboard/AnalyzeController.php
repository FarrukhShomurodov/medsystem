<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeRequest;
use App\Models\Analyze;
use App\Models\AnalyzeMethod;
use App\Models\AnalyzeType;
use App\Models\Equipment;
use App\Models\ExaminationMap;
use App\Models\Laboratory;
use App\Models\Patient;
use App\Services\AnalyzeService;
use App\Traits\HandlesStaffAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AnalyzeController extends Controller
{
    use HandlesStaffAvailability;

    protected AnalyzeService $service;

    public function __construct(AnalyzeService $service)
    {
        $this->middleware(['permission:list_analyzes'])->only('index');
        $this->middleware(['permission:create_analyzes'])->only(['create', 'store']);
        $this->middleware(['permission:edit_analyzes'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_analyzes'])->only('destroy');

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
        $doctors = $this->scopeAvailableStaffOnDate(1);
        $laboratories = Laboratory::query()->get();
        $equipments = Equipment::query()->where('status', 'работает')->get();
        $methods = AnalyzeMethod::query()->get();
        $patients = Patient::query()->get();
        $maps = ExaminationMap::query()->where('is_active', 1)->get();

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
        $doctors = $this->scopeAvailableStaffOnDate(1);
        $laboratories = Laboratory::query()->get();
        $equipments = Equipment::query()->where('status', 'работает')->get();
        $methods = AnalyzeMethod::query()->get();
        $patients = Patient::query()->get();
        $maps = $analyze->patient->examinationMaps;

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
