<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
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
            $patients = Patient::query()->paginate(10);
        }

        return view('admin.sister_window.index', compact('patients'));
    }
}
