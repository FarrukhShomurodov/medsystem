<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\AllegoryRequest;
use App\Models\Allegory;
use App\Services\AllegoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AllegoryController
{
    protected AllegoryService $service;

    public function __construct(AllegoryService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $allegories = Allegory::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.allegories.index', compact('allegories'));
    }

    public function create(): View
    {
        return view('admin.allegories.create');
    }

    public function store(AllegoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('allegories.index')->with('success', 'Тип аллегрии успешно добавлен!');
    }

    public function edit(Allegory $allegory): View
    {
        return view('admin.allegories.edit', compact('allegory'));
    }

    public function update(AllegoryRequest $request, Allegory $allegory): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($allegory, (array)$validated);

        return redirect()->route('allegories.index')->with('success', 'Тип аллегрии успешно обновлен!');
    }

    public function destroy(Allegory $allegory): RedirectResponse
    {
        $this->service->destroy($allegory);

        return redirect()->route('allegories.index')->with('success', 'Тип аллегрии успешно удален!');
    }
}
