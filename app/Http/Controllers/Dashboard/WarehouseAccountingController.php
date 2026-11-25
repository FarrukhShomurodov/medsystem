<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WarehouseAccounting;
use Illuminate\Contracts\View\View;

class WarehouseAccountingController extends Controller
{
    /** A medication batch of a department warehouse. */
    public function show(WarehouseAccounting $accounting): View
    {
        $accounting->load(['warehouse', 'medication', 'packaging', 'medicationCost.supplier']);

        return view('admin.warehouses.medication_show', compact('accounting'));
    }
}
