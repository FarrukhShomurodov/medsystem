<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeMethodRequest;
use App\Models\AnalyzeMethod;
use App\Services\AnalyzeMethodService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AnalyzeMethodController extends Controller
{
    protected AnalyzeMethodService $service;

    public function __construct(AnalyzeMethodService $service)
    {
        $this->middleware(['permission:view_analyze_methods'])->only('index');
        $this->middleware(['permission:create_analyze_methods'])->only(['create', 'store']);
        $this->middleware(['permission:edit_analyze_methods'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_analyze_methods'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $methods = AnalyzeMethod::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('admin.methods.create');
    }

    public function store(AnalyzeMethodRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('methods.index')->with('success', 'Метод анализа успешно добавлен!');
    }

    public function edit(AnalyzeMethod $method): View
    {
        return view('admin.methods.edit', compact('method'));
    }

    public function update(AnalyzeMethodRequest $request, AnalyzeMethod $method): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($method, (array)$validated);

        return redirect()->route('methods.index')->with('success', 'Метод анализа успешно обновлен!');
    }

    public function destroy(AnalyzeMethod $method): RedirectResponse
    {
        $this->service->destroy($method);

        return redirect()->route('methods.index')->with('success', 'Метод анализа успешно удален!');
    }
}
