<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeRequest;
use App\Models\Floor;
use App\Models\Office;
use App\Services\OfficeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OfficeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OfficeService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_offices', only: ['index']),
            new Middleware('permission:create_offices', only: ['create', 'store']),
            new Middleware('permission:edit_offices', only: ['edit', 'update']),
            new Middleware('permission:delete_offices', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $offices = Office::query()
            ->with('floor')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.offices.index', compact('offices'));
    }

    public function create(): View
    {
        $floors = Floor::query()->get();

        return view('admin.offices.create', compact('floors'));
    }

    public function store(OfficeRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('offices.index')->with('success', 'Кабинет успешно добавлен!');
    }

    public function edit(Office $office): View
    {
        $floors = Floor::query()->get();

        return view('admin.offices.edit', compact('office', 'floors'));
    }

    public function update(OfficeRequest $request, Office $office): RedirectResponse
    {
        $this->service->update($office, $request->validated());

        return to_route('offices.index')->with('success', 'Кабинет успешно обновлен!');
    }

    public function destroy(Office $office): RedirectResponse
    {
        $this->service->destroy($office);

        return to_route('offices.index')->with('success', 'Кабинет успешно удален!');
    }
}
