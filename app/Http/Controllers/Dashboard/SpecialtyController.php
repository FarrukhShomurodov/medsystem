<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialtyRequest;
use App\Models\Specialty;
use App\Services\SpecialtyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SpecialtyController extends Controller implements HasMiddleware
{
    public function __construct(private readonly SpecialtyService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_specialties', only: ['index']),
            new Middleware('permission:create_specialties', only: ['create', 'store']),
            new Middleware('permission:edit_specialties', only: ['edit', 'update']),
            new Middleware('permission:delete_specialties', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $specialties = Specialty::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.specialties.index', compact('specialties'));
    }

    public function create(): View
    {
        return view('admin.specialties.create');
    }

    public function store(SpecialtyRequest $request): RedirectResponse
    {
        $this->service->store($request->specialty());

        return to_route('specialties.index')->with('success', 'Специальность успешно добавлена!');
    }

    public function edit(Specialty $specialty): View
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(SpecialtyRequest $request, Specialty $specialty): RedirectResponse
    {
        $this->service->update($specialty, $request->specialty());

        return to_route('specialties.index')->with('success', 'Специальность успешно обновлена!');
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        $this->service->destroy($specialty);

        return to_route('specialties.index')->with('success', 'Специальность успешно удалена!');
    }
}
