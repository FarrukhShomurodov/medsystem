<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChronicDiseaseRequest;
use App\Models\ChronicDisease;
use App\Services\ChronicDiseaseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ChronicDiseaseController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ChronicDiseaseService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_diseases', only: ['index']),
            new Middleware('permission:create_diseases', only: ['create', 'store']),
            new Middleware('permission:edit_diseases', only: ['edit', 'update']),
            new Middleware('permission:delete_diseases', only: ['destroy']),
        ];
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
        $this->service->store($request->validated());

        return to_route('diseases.index')->with('success', 'Хроническая болезнь успешно добавлена!');
    }

    public function edit(ChronicDisease $disease): View
    {
        return view('admin.diseases.edit', compact('disease'));
    }

    public function update(ChronicDiseaseRequest $request, ChronicDisease $disease): RedirectResponse
    {
        $this->service->update($disease, $request->validated());

        return to_route('diseases.index')->with('success', 'Хроническая болезнь успешно обновлена!');
    }

    public function destroy(ChronicDisease $disease): RedirectResponse
    {
        $this->service->destroy($disease);

        return to_route('diseases.index')->with('success', 'Хроническая болезнь успешно удалена!');
    }
}
