<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\ServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Services\ServicCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceCategoryController
{
    protected ServicCategoryService $service;

    public function __construct(ServicCategoryService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $categories = ServiceCategory::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(ServiceCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('categories.index')->with('success', 'Категория сервиса успешно добавлена!');
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(ServiceCategoryRequest $request, ServiceCategory $category): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($category, (array)$validated);

        return redirect()->route('categories.index')->with('success', 'Категория сервиса успешно обновлена!');
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        $this->service->destroy($category);

        return redirect()->route('categories.index')->with('success', 'Категория сервиса успешно удалена!');

    }
}
