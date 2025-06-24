<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientParameter;
use Illuminate\Http\Request;

class PatientParameterController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'systolic' => 'nullable|integer',
            'diastolic' => 'nullable|integer',
            'pulse' => 'nullable|integer',
            'temperature' => 'nullable|numeric',
            'saturation' => 'nullable|integer',
            'breathing_rate' => 'nullable|integer',
            'consciousness' => 'nullable|string',
            'oxygen_type' => 'nullable|string',
            'pain_score' => 'nullable|integer',
            'fall_risk' => 'nullable|integer',
            'urine_balance' => 'nullable|integer',
            'total_score' => 'nullable|integer',
        ]);

        PatientParameter::create($data);

        return redirect()->back()->with('success', 'Данные успешно сохранены');
    }

}
