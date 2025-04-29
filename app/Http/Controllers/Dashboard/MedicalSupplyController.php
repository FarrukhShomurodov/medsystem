<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalSupplyRequest;
use App\Models\MedicalSupply;
use App\Services\MedicalSupplyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MedicalSupplyController extends Controller
{
    protected MedicalSupplyService $service;

    public function __construct(MedicalSupplyService $service)
    {
        $this->middleware(['permission:view_supplies'])->only('index');
        $this->middleware(['permission:create_supplies'])->only(['create', 'store']);
        $this->middleware(['permission:edit_supplies'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_supplies'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $supplies = MedicalSupply::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.supplies.index', compact('supplies'));
    }

    public function create(): View
    {
        return view('admin.supplies.create');
    }

    public function store(MedicalSupplyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('supplies.index')->with('success', 'Расходный материал успешно добавлен!');
    }

    public function edit(MedicalSupply $supply): View
    {
        return view('admin.supplies.edit', compact('supply'));
    }

    public function update(MedicalSupplyRequest $request, MedicalSupply $supply): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($supply, (array)$validated);

        return redirect()->route('supplies.index')->with('success', 'Расходный материал успешно обновлен!');
    }

    public function destroy(MedicalSupply $supply): RedirectResponse
    {
        $this->service->destroy($supply);

        return redirect()->route('supplies.index')->with('success', 'Расходный материал успешно удален!');
    }
}
