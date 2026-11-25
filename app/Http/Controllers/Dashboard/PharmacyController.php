<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PharmacyRequest;
use App\Models\Pharmacy;
use App\Models\PharmacyType;
use App\Services\PharmacyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PharmacyController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PharmacyService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_pharmacies', only: ['index']),
            new Middleware('permission:create_pharmacies', only: ['create', 'store']),
            new Middleware('permission:edit_pharmacies', only: ['edit', 'update']),
            new Middleware('permission:delete_pharmacies', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $pharmacies = Pharmacy::query()
            ->select(['id', 'name', 'type_id'])
            ->with('type')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.pharmacies.index', compact('pharmacies'));
    }

    public function medications(Pharmacy $pharmacy): View
    {
        $medications = $pharmacy->medication()->orderBy('name')->get();

        return view('admin.pharmacies.medications', compact('pharmacy', 'medications'));
    }

    public function create(): View
    {
        $types = PharmacyType::query()->get();

        return view('admin.pharmacies.create', compact('types'));
    }

    public function store(PharmacyRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('pharmacies.index')->with('success', 'Аптека успешно добавлена!');
    }

    public function edit(Pharmacy $pharmacy): View
    {
        $types = PharmacyType::query()->get();

        return view('admin.pharmacies.edit', compact('pharmacy', 'types'));
    }

    public function update(PharmacyRequest $request, Pharmacy $pharmacy): RedirectResponse
    {
        $this->service->update($pharmacy, $request->validated());

        return to_route('pharmacies.index')->with('success', 'Аптека успешно обновлена!');
    }

    public function destroy(Pharmacy $pharmacy): RedirectResponse
    {
        $this->service->destroy($pharmacy);

        return to_route('pharmacies.index')->with('success', 'Аптека успешно удалена!');
    }
}
