<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MedicationAdditionalSubstance;
use Illuminate\Http\RedirectResponse;

class MedicationAdditionalSubstanceController extends Controller
{
    public function delete(MedicationAdditionalSubstance $substance): RedirectResponse
    {
        $substance->delete();
        return redirect()->back()->with('success', 'Вспомогательное вещество удалено!');
    }
}
