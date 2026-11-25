<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllergyRequest;
use App\Models\Allergy;
use App\Services\AllergyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * The allergy reference list. Its route names, views and permissions keep the historical
 * "allegories" spelling, which is stored data (permissions) and part of the URLs.
 */
class AllergyController extends Controller implements HasMiddleware
{
    public function __construct(private readonly AllergyService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_allegories', only: ['index']),
            new Middleware('permission:create_allegories', only: ['create', 'store']),
            new Middleware('permission:edit_allegories', only: ['edit', 'update']),
            new Middleware('permission:delete_allegories', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $allergies = Allergy::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.allegories.index', ['allegories' => $allergies]);
    }

    public function create(): View
    {
        return view('admin.allegories.create');
    }

    public function store(AllergyRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('allegories.index')->with('success', 'Аллергия успешно добавлена!');
    }

    public function edit(Allergy $allergy): View
    {
        return view('admin.allegories.edit', ['allegory' => $allergy]);
    }

    public function update(AllergyRequest $request, Allergy $allergy): RedirectResponse
    {
        $this->service->update($allergy, $request->validated());

        return to_route('allegories.index')->with('success', 'Аллергия успешно обновлена!');
    }

    public function destroy(Allergy $allergy): RedirectResponse
    {
        $this->service->destroy($allergy);

        return to_route('allegories.index')->with('success', 'Аллергия успешно удалена!');
    }
}
