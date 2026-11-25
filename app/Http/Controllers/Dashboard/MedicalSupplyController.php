<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalSupplyRequest;
use App\Models\MedicalSupply;
use App\Services\MedicalSupplyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MedicalSupplyController extends Controller implements HasMiddleware
{
    public function __construct(private readonly MedicalSupplyService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_supplies', only: ['index']),
            new Middleware('permission:create_supplies', only: ['create', 'store']),
            new Middleware('permission:edit_supplies', only: ['edit', 'update']),
            new Middleware('permission:delete_supplies', only: ['destroy']),
        ];
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
        $this->service->store($request->validated());

        return to_route('supplies.index')->with('success', 'Расходный материал успешно добавлен!');
    }

    public function edit(MedicalSupply $supply): View
    {
        return view('admin.supplies.edit', compact('supply'));
    }

    public function update(MedicalSupplyRequest $request, MedicalSupply $supply): RedirectResponse
    {
        $this->service->update($supply, $request->validated());

        return to_route('supplies.index')->with('success', 'Расходный материал успешно обновлен!');
    }

    public function destroy(MedicalSupply $supply): RedirectResponse
    {
        $this->service->destroy($supply);

        return to_route('supplies.index')->with('success', 'Расходный материал успешно удален!');
    }
}
