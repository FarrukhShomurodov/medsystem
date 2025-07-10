<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplyRequest;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;

class SupplyRequestController extends Controller
{
    protected $service;

    public function __construct(SupplyRequestService $service)
    {
        $this->service = $service;
    }

    public function store(StoreSupplyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->store((array)$validated);

        return redirect()->back()->with('success', 'Заказ успешно создан!');
    }
}
