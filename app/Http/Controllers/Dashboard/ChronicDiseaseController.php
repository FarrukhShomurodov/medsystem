<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChronicDiseaseRequest;
use App\Models\ChronicDisease;
use App\Services\ChronicDiseaseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ChronicDiseaseController extends Controller
{
    protected ChronicDiseaseService $service;

    public function __construct(ChronicDiseaseService $service)
    {
        $this->middleware(['permission:list_diseases'])->only('index');
        $this->middleware(['permission:create_diseases'])->only(['create', 'store']);
        $this->middleware(['permission:edit_diseases'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_diseases'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $diseases = ChronicDisease::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.diseases.index', compact('diseases'));
    }

    public function create(): View
    {
        return view('admin.diseases.create');
    }

    public function store(ChronicDiseaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('diseases.index')->with('success', 'Хроническая болезнь успешно добавлена!');
    }

    public function edit(ChronicDisease $disease): View
    {
        return view('admin.diseases.edit', compact('disease'));
    }

    public function update(ChronicDiseaseRequest $request, ChronicDisease $disease): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($disease, (array)$validated);

        return redirect()->route('diseases.index')->with('success', 'Хроническая болезнь успешно обновлена!');
    }

    public function destroy(ChronicDisease $disease): RedirectResponse
    {
        $this->service->destroy($disease);

        return redirect()->route('diseases.index')->with('success', 'Хроническая болезнь успешно удалена!');
    }
}
