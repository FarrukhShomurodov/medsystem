<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentResponse;
use App\Models\ExaminationMap;
use App\Models\PatientAssignment;
use App\Models\PatientDiagnosis;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * The detail partial of a row of the patient card's visit history.
 */
class PatientHistoryController extends Controller
{
    /**
     * Row type => the record it shows, the relations its partial renders, and the partial.
     *
     * @var array<string, array{0: class-string<Model>, 1: list<string>, 2: view-string}>
     */
    private const array RECORDS = [
        'examination_map' => [ExaminationMap::class, ['department', 'doctor'], 'admin.patients.history.examination_map'],
        'assignment' => [PatientAssignment::class, ['staff', 'service'], 'admin.patients.history.assignment'],
        'service' => [PatientService::class, ['service', 'surveyResponses.survey', 'surveyResponses.answers.question'], 'admin.patients.history.service'],
        'patient_med_document' => [SurveyResponse::class, ['survey', 'answers.question'], 'admin.patients.history.patient_med_document'],
        'reception' => [Reception::class, ['staff', 'patient'], 'admin.patients.history.reception'],
        'diagnosis' => [PatientDiagnosis::class, ['diagnosis'], 'admin.patients.history.diagnosis'],
        'document' => [DocumentResponse::class, ['document.questions', 'answers'], 'admin.patients.history.document'],
        'medication' => [PatientMedication::class, ['medication.composition', 'medication.group', 'compatibleMedications.composition'], 'admin.patients.history.medication'],
        'procedure' => [PatientProcedure::class, ['procedure'], 'admin.patients.history.procedure'],
    ];

    /** The admission and the discharge row of a stay open the same partial. */
    private const array ALIASES = [
        'examination_map_start' => 'examination_map',
        'examination_map_end' => 'examination_map',
    ];

    public function partial(Request $request): Response|JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in([...array_keys(self::RECORDS), ...array_keys(self::ALIASES)])],
            'id' => ['required', 'integer'],
        ]);

        $type = self::ALIASES[$validated['type']] ?? $validated['type'];
        [$model, $relations, $partial] = self::RECORDS[$type];
        $record = $model::query()->with($relations)->find($validated['id']);

        if ($record === null) {
            return response()->json(['error' => 'Запись не найдена.'], 404);
        }

        return response(view($partial, ['data' => $record])->render());
    }
}
