<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use App\Exports\PatientRecordsExport;
use Maatwebsite\Excel\Facades\Excel;

class RegistrarController extends Controller
{
    public function export(Patient $patient)
    {
        $allRecords = collect();

        foreach ($patient->allServices->where('status', 'assigned') as $s) {
            $allRecords->push([
                'id' => $s->id,
                'type' => 'service',
                'name' => $s->service?->name ?? '-',
                'price' => (float)($s->service?->price ?? 0),
                'status' => $s->status,
                'source' => $s,
            ]);
        }

        // Приёмы (Reception)
        foreach ($patient->allReceptions->where('status', 'new') as $r) {
            $allRecords->push([
                'id' => $r->id,
                'type' => 'reception',
                'name' => $r->service?->name ?? '-',
                'price' => (float)($r->service?->price ?? 0),
                'status' => $r->status,
                'source' => $r,
            ]);
        }

        foreach ($patient->allProcedures->where('status', 'assigned') as $p) {
            $allRecords->push([
                'id' => $p->id,
                'type' => 'procedure',
                'name' => $p->procedure?->name ?? '-',
                'price' => (float)($p->procedure?->price ?? 0),
                'status' => $p->status,
                'source' => $p,
            ]);
        }

        foreach (PatientMedication::query()->where('status', 'ordered')->where('patient_id', $patient->id)->with(['medication', 'medication.mainWarehouses', 'medication.mainWarehouses.medication'])->get() as $p) {
            $allRecords->push([
                'id' => $p->id,
                'type' => 'medication',
                'name' => $p->medication->name ?? '-',
                'price' => $p->price,
                'status' => $p->status,
                'source' => $p,
            ]);
        }


        // Сортируем по дате создания (если нужно) — у каждого source есть created_at
        $allRecords = $allRecords->sortByDesc(function ($item) {
            return optional($item['source'])->created_at ?? null;
        })->values();

        // Считаем общую сумму
        $total = $allRecords->sum(fn($it) => $it['price'] ?? 0);


        return Excel::download(new PatientRecordsExport($allRecords, $total), 'patient_records.xlsx');
    }

}
