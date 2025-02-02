<?php

namespace App\Services;

use App\Models\ServiceCategory;

class ServicCategoryService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ServiceCategory
    {
        return ServiceCategory::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ServiceCategory $category, array $validated): ServiceCategory
    {
        $category->update($validated);
        return $category->refresh();
    }

    /**
     * @param ServiceCategory $category
     * @return void
     */
    public function destroy(ServiceCategory $category): void
    {
        $category->delete();
    }
}
