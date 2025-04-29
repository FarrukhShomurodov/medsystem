<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeTypeRequest;
use App\Models\AnalyzeType;
use App\Services\AnalyzeTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AnalyzeTypeController extends Controller
{
    protected AnalyzeTypeService $service;

    public function __construct(AnalyzeTypeService $service)
    {
        $this->middleware(['permission:view_analyze_types'])->only('index');
        $this->middleware(['permission:create_analyze_types'])->only(['create', 'store']);
        $this->middleware(['permission:edit_analyze_types'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_analyze_types'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $types = AnalyzeType::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.types.create');
    }

    public function store(AnalyzeTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('types.index')->with('success', 'Тип анализа успешно добавлен!');
    }

    public function edit(AnalyzeType $type): View
    {
        return view('admin.types.edit', compact('type'));
    }

    public function update(AnalyzeTypeRequest $request, AnalyzeType $type): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($type, (array)$validated);

        return redirect()->route('types.index')->with('success', 'Тип анализа успешно обновлен!');
    }

    public function destroy(AnalyzeType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return redirect()->route('types.index')->with('success', 'Тип анализа успешно удален!');

    }
}
