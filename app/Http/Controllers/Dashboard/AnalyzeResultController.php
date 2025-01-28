<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\AnalyzeResultRequest;
use App\Models\Analyze;
use App\Services\AnalyzeResultService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AnalyzeResultController
{
    protected AnalyzeResultService $service;

    public function __construct(AnalyzeResultService $service)
    {
        $this->service = $service;
    }

    public function show(Analyze $analyze): View
    {
        $results = $analyze->results;
        return view('admin.analyze_results.show', compact('results'));
    }

    public function create(Analyze $analyze): View
    {
        $analyze->with('types');
        return view('admin.analyze_results.create', compact('analyze'));
    }

    public function store(AnalyzeResultRequest $request, Analyze $analyze): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store($validated, $analyze);
        return redirect()->route('analyzes.index')->with('success', 'Резултаты успешно добавлены!');
    }

    public function edit(Analyze $analyze): View
    {
        $analyze->with('results');
        return view('admin.analyze_results.edit', compact('analyze'));
    }

    public function update(AnalyzeResultRequest $request, Analyze $analyze): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($validated, $analyze);
        return redirect()->route('analyze.result.show', $analyze->id)->with('success', 'Резултаты успешно обновлены!');
    }
}
