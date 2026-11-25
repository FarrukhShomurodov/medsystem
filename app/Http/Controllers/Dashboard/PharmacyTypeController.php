<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PharmacyTypeRequest;
use App\Models\PharmacyType;
use App\Services\PharmacyTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PharmacyTypeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PharmacyTypeService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_pharmacy_types', only: ['index']),
            new Middleware('permission:create_pharmacy_types', only: ['create', 'store']),
            new Middleware('permission:edit_pharmacy_types', only: ['edit', 'update']),
            new Middleware('permission:delete_pharmacy_types', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $types = PharmacyType::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.pharmacy_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.pharmacy_types.create');
    }

    public function store(PharmacyTypeRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('pharmacy.types.index')->with('success', 'Тип аптеки успешно добавлен!');
    }

    public function edit(PharmacyType $type): View
    {
        return view('admin.pharmacy_types.edit', compact('type'));
    }

    public function update(PharmacyTypeRequest $request, PharmacyType $type): RedirectResponse
    {
        $this->service->update($type, $request->validated());

        return to_route('pharmacy.types.index')->with('success', 'Тип аптеки успешно обновлен!');
    }

    public function destroy(PharmacyType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return to_route('pharmacy.types.index')->with('success', 'Тип аптеки успешно удален!');
    }
}
