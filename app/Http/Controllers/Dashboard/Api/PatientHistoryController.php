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
use App\Models\SurveyResponses;
use Illuminate\Http\Request;

class PatientHistoryController extends Controller
{
    public function partial(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        if (!$type || !$id) {
            return response()->json(['error' => 'Type and ID parameters are required'], 400);
        }

        if ($type == 'examination_map_start' || $type == 'examination_map_end') {
            $type = 'examination_map';
        }

        // Получаем данные в зависимости от типа
        switch ($type) {
            case 'examination_map':
                $data = ExaminationMap::with(['department'])->find($id);
                break;
            case 'assignment':
                $data = PatientAssignment::with(['staff', 'service'])->find($id);
                break;
            case 'service':
                $data = PatientService::with(['doctor'])->find($id);
                break;
            case 'patient_med_document':
                $data = SurveyResponses::query()->find($id);
                break;
            case 'reception':
                $data = Reception::with(['staff', 'patient'])->find($id);
                break;
            case 'diagnosis':
                $data = PatientDiagnosis::find($id);
                break;
            case 'document':
                $data = DocumentResponse::find($id);
                break;
            case 'medication':
                $data = PatientMedication::find($id);
                break;
            case 'procedure':
                $data = PatientProcedure::find($id);
                break;
            default:
                return response()->json(['error' => 'Unknown type'], 400);
        }

        if (!$data) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        if (!view()->exists('admin.patients.history.' . $type)) {
            return response()->json(['error' => 'View not found'], 404);
        }

        return view('admin.patients.history.' . $type, ['data' => $data])->render();
    }
}
