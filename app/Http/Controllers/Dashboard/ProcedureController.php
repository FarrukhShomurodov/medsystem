<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcedureRequest;
use App\Models\Procedure;
use App\Services\ProcedureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProcedureController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ProcedureService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_service_procedures', only: ['index']),
            new Middleware('permission:create_service_procedures', only: ['create', 'store']),
            new Middleware('permission:edit_service_procedures', only: ['edit', 'update']),
            new Middleware('permission:delete_service_procedures', only: ['destroy']),
        ];
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
        $this->service->store($validated);

        return to_route('procedures.index')->with('success', 'Процедура успешно добавлена!');
    }

    public function edit(Procedure $procedure): View
    {
        return view('admin.procedures.edit', compact('procedure'));
    }

    public function update(ProcedureRequest $request, Procedure $procedure): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($procedure, $validated);

        return to_route('procedures.index')->with('success', 'Процедура успешно обновлена!');
    }

    public function destroy(Procedure $procedure): RedirectResponse
    {
        $this->service->destroy($procedure);

        return to_route('procedures.index')->with('success', 'Процедура успешно удалена!');
    }
}
