<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientParameter;
use App\Models\Staff;
use Illuminate\Contracts\View\View;

class SisterWindowController extends Controller
{
    public function index(): View
    {
        $roles = auth('admin')->user()->getRoleNames();

        if ($roles->contains('Медсестра')) {
            $staffId = auth('admin')->user()->staff_id;

            $staff = Staff::with('professionalData.department.examinationMaps.patient')->find($staffId);

            $examinationMaps = $staff->professionalData->department->examinationMaps;

            $patients = $examinationMaps->map(function ($map) {
                return $map->patient;
            })->unique('id')->values();
        } else {
            $patients = Patient::query()->whereHas('examinationMaps', function ($q) {
                $q->where('is_active', true);
            })->paginate(10);
        }

        return view('admin.sister_window.index', compact('patients'));
    }

    public function show(Patient $patient): View
    {
        $roles = auth('admin')->user()->getRoleNames();

        if ($roles->contains('Медсестра')) {
            $staffId = auth('admin')->user()->staff_id;

            $staff = Staff::with('professionalData.department.examinationMaps.patient')->find($staffId);

            $examinationMaps = $staff->professionalData->department->examinationMaps;

            $patients = $examinationMaps->map(function ($map) {
                return $map->patient;
            })->unique('id')->values();
        } else {
            $patients = Patient::query()->whereHas('examinationMaps', function ($q) {
                $q->where('is_active', true);
            })->paginate(10);
        }


        $records = PatientParameter::where('patient_id', $patient->id)->get();

        $menu = [
            'general' => ['label' => 'Общие данные', 'graphic' => true],
            'breathing_rate' => ['label' => 'ЧДД', 'graphic' => false],
            'consciousness' => ['label' => 'Сознание', 'graphic' => false],
            'oxygen_type' => ['label' => 'Кислород/воздух', 'graphic' => false],
            'total_score' => ['label' => 'Общий балл', 'graphic' => false],
        ];

        return view('admin.sister_window.show', compact('patient', 'records', 'menu', 'patients'));
    }
}
