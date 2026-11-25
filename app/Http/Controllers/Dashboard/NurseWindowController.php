<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Document;
use App\Models\Patient;
use App\Models\VitalParameter;
use App\Queries\Patients\NursePatientsQuery;
use App\Queries\Patients\VitalSignsChart;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;

/**
 * The nurse window: the inpatients of the employee's department, their vital signs, scored
 * documents and the treatment plan of the stay.
 */
class NurseWindowController extends Controller
{
    /** Sections of the vital signs panel. */
    private const array MENU = [
        'general' => ['label' => 'Общие данные', 'graphic' => true],
        'breathing_rate' => ['label' => 'ЧДД', 'graphic' => false],
        'consciousness' => ['label' => 'Сознание', 'graphic' => false],
        'oxygen_type' => ['label' => 'Кислород/воздух', 'graphic' => false],
        'total_score' => ['label' => 'Общий балл', 'graphic' => false],
    ];

    public function index(#[CurrentUser(Admin::GUARD)] Admin $nurse): View
    {
        return view('admin.sister_window.index', [
            'patients' => NursePatientsQuery::for($nurse)->paginate(),
        ]);
    }

    public function show(Patient $patient, VitalSignsChart $chart, #[CurrentUser(Admin::GUARD)] Admin $nurse): View|RedirectResponse
    {
        $nursePatients = NursePatientsQuery::for($nurse);
        $examinationMap = $nursePatients->activeMapOf($patient);

        if ($examinationMap === null) {
            return to_route('sister.window.index')
                ->with('warning', 'У пациента нет открытой госпитализации в вашем отделении.');
        }

        // The treatment plan of the stay: the orders made during it carry its id.
        $examinationMap->load([
            'documentResponses',
            'patientMedications' => fn (HasMany $prescriptions) => $prescriptions->with('medication:id,name')->oldest(),
            'patientProcedures' => fn (HasMany $procedures) => $procedures->with('procedure:id,name')->orderBy('procedure_date'),
            'services' => fn (HasMany $services) => $services->orderBy('service_date')->orderBy('service_time'),
        ]);
        $vitalParameters = VitalParameter::query()->orderBy('id')->get(['id', 'name']);

        return view('admin.sister_window.show', [
            'patient' => $patient,
            'patients' => $nursePatients->paginate(),
            'examinationMap' => $examinationMap,
            'menu' => self::MENU,
            'vitalParameters' => $vitalParameters,
            ...$chart->for($examinationMap, $vitalParameters),
            'documents' => Document::query()
                ->whereKeyNot(Document::INITIAL_NURSING_ASSESSMENT)
                ->orderBy('id')
                ->get(['id', 'title']),
        ]);
    }
}
