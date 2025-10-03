<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeRequest;
use App\Models\Floor;
use App\Models\Office;
use App\Services\OfficeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OfficeController extends Controller
{
    protected OfficeService $service;

    public function __construct(OfficeService $service)
    {
        $this->middleware(['permission:list_offices'])->only('index');
        $this->middleware(['permission:create_offices'])->only(['create', 'store']);
        $this->middleware(['permission:edit_offices'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_offices'])->only('destroy');

        $this->service = $service;
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
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('offices.index')->with('success', 'Кабинет  успешно добавлен!');
    }

    public function edit(Office $office): View
    {
        $floors = Floor::query()->get();

        return view('admin.offices.edit', compact('office', 'floors'));
    }

    public function update(OfficeRequest $request, Office $office): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($office, (array)$validated);

        return redirect()->route('offices.index')->with('success', 'Кабинет  успешно обновлен!');
    }

    public function destroy(Office $office): RedirectResponse
    {
        $this->service->destroy($office);

        return redirect()->route('offices.index')->with('success', 'Кабинет  успешно удален!');
    }
}
