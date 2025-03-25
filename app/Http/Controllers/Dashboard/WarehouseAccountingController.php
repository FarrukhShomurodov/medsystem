<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\WarehouseAccounting;
use Illuminate\Contracts\View\View;

class WarehouseAccountingController
{
    public function show(WarehouseAccounting $accounting): View
    {
        return view('admin.warehouses.medication_show', compact('accounting'));
    }
}
