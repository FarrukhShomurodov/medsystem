<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Http\Requests\ProcedureRequest;
use App\Models\Procedure;
use App\Services\ProcedureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProcedureController extends Controller
{
    protected ProcedureService $service;

    public function __construct(ProcedureService $service)
    {
        $this->middleware(['permission:view_service_procedures'])->only('index');
        $this->middleware(['permission:create_service_procedures'])->only(['create', 'store']);
        $this->middleware(['permission:edit_service_procedures'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_service_procedures'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $procedures = Procedure::query()
            ->select(['id', 'name', 'price'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.procedures.index', compact('procedures'));
    }

    public function create(): View
    {
        return view('admin.procedures.create');
    }

    public function store(ProcedureRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('procedures.index')->with('success', 'Процедура успешно добавлена!');
    }

    public function edit(Procedure $procedure): View
    {
        return view('admin.procedures.edit', compact('procedure'));
    }

    public function update(ProcedureRequest $request, Procedure $procedure): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($procedure, (array)$validated);

        return redirect()->route('procedures.index')->with('success', 'Процедура успешно обновлена!');
    }

    public function destroy(Procedure $procedure): RedirectResponse
    {
        $this->service->destroy($procedure);

        return redirect()->route('procedures.index')->with('success', 'Процедура успешно удалена!');
    }
}
