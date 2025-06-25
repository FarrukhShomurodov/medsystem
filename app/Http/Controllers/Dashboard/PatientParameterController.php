<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientParameterController extends Controller
{
    public function storeNormalVitalParameter(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'parameters' => 'required|array',
        ]);

        foreach ($validated['parameters'] as $paramId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($patient->vitalNormalParameters()->wherePivot('vital_parameter_id', $paramId)->exists()) {
                $patient->vitalNormalParameters()->updateExistingPivot($paramId, ['normal' => $value]);
            } else {
                $patient->vitalNormalParameters()->attach($paramId, ['normal' => $value]);
            }
        }

        return redirect()->back()->with('success', 'Нормальные показатели успешно сохранены!');
    }

    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'parameters' => 'required|array',
        ]);

        $now = now();

        $examinationMap = $patient->examinationMaps->where('is_active', true)->first();

        foreach ($data['parameters'] as $paramId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $examinationMap->vitalParameters()->attach($paramId, [
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return redirect()->back()->with('success', 'Данные успешно сохранены');
    }
}
