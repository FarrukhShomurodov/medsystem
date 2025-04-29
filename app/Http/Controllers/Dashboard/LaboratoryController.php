<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Http\Requests\LaboratoryRequest;
use App\Models\Laboratory;
use App\Services\LaboratoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LaboratoryController extends Controller
{
    private LaboratoryService $service;

    public function __construct(LaboratoryService $service)
    {
        $this->middleware(['permission:view_laboratories'])->only('index');
        $this->middleware(['permission:create_laboratories'])->only(['create', 'store']);
        $this->middleware(['permission:edit_laboratories'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_laboratories'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $laboratories = Laboratory::query()
            ->select(['id', 'name', 'address', 'phone'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.laboratories.index', compact('laboratories'));
    }

    public function create(): View
    {
        return view('admin.laboratories.create');
    }

    public function store(LaboratoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('laboratories.index')->with('success', 'Лаборатория успешно добавлена!');
    }

    public function edit(Laboratory $laboratory): View
    {
        return view('admin.laboratories.edit', compact('laboratory'));
    }

    public function update(LaboratoryRequest $request, Laboratory $laboratory): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($laboratory, (array)$validated);

        return redirect()->route('laboratories.index')->with('success', 'Лаборатория успешно обновлена!');
    }

    public function destroy(Laboratory $laboratory): RedirectResponse
    {
        $this->service->destroy($laboratory);

        return redirect()->route('laboratories.index')->with('success', 'Лаборатория успешно удалена!');
    }
}
