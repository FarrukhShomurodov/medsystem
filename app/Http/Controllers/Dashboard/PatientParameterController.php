<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\VitalParametersRequest;
use App\Models\Admin;
use App\Models\Patient;
use App\Queries\Patients\NursePatientsQuery;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PatientParameterController extends Controller
{
    /**
     * The patient's normal values (patient card): a value replaces the previous normal of that parameter.
     */
    public function storeNormalVitalParameter(VitalParametersRequest $request, Patient $patient): RedirectResponse
    {
        $normals = array_map(fn (string $value): array => ['normal' => $value], $request->filledValues());

        DB::transaction(fn () => $patient->vitalNormalParameters()->syncWithoutDetaching($normals));

        return back()->with('success', 'Нормальные показатели успешно сохранены!');
    }

    /**
     * A measurement of the nurse window, recorded on the patient's open stay.
     */
    public function store(VitalParametersRequest $request, Patient $patient, #[CurrentUser(Admin::GUARD)] Admin $nurse): RedirectResponse
    {
        $map = NursePatientsQuery::for($nurse)->activeMapOf($patient);

        if ($map === null) {
            return back()->with('error', 'У пациента нет открытой госпитализации в вашем отделении: показатели не сохранены.');
        }

        $measuredAt = now();
        $map->vitalParameters()->attach(array_map(
            fn (string $value): array => ['value' => $value, 'created_at' => $measuredAt, 'updated_at' => $measuredAt],
            $request->filledValues(),
        ));

        return back()->with('success', 'Данные успешно сохранены');
    }
}
