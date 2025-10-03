<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\CashierStatsExport;
use App\Http\Controllers\Controller;
use App\Models\PatientMedication;
use App\Models\PatientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\Reception;
use App\Models\PatientProcedure;
use Maatwebsite\Excel\Facades\Excel;


class CashierStatsController extends Controller
{
    public function index(Request $request): View
    {
        // PatientService
        $servicesQuery = PatientService::with(['service', 'patient', 'doctor']);
        if ($request->filled('first_name')) {
            $servicesQuery->whereHas('patient', fn($q) => $q->where('first_name', 'like', '%'.$request->first_name.'%'));
        }
        if ($request->filled('last_name')) {
            $servicesQuery->whereHas('patient', fn($q) => $q->where('last_name', 'like', '%'.$request->last_name.'%'));
        }
        if ($request->filled('status')) {
            $servicesQuery->where('status', $request->status);
        }
        if ($request->filled('service_date')) {
            $servicesQuery->whereDate('service_date', $request->service_date);
        }
        $services = $servicesQuery->where('status', 'confirmed')->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'type' => 'service',
                'name' => $s->service?->name,
                'patient' => $s->patient?->last_name . ' ' . $s->patient?->first_name,
                'doctor' => $s->doctor?->full_name,
                'price' => $s->service?->price,
                'currency' => $s->service?->currency ?? 'UZS',
                'date_time' => trim($s->service_date . ' ' . $s->service_time),
                'status' => $s->getStatusBadge(),
            ];
        });

        // Reception
        $receptionsQuery = Reception::with(['service', 'patient', 'staff']);
        if ($request->filled('first_name')) {
            $receptionsQuery->whereHas('patient', fn($q) => $q->where('first_name', 'like', '%'.$request->first_name.'%'));
        }
        if ($request->filled('last_name')) {
            $receptionsQuery->whereHas('patient', fn($q) => $q->where('last_name', 'like', '%'.$request->last_name.'%'));
        }
        if ($request->filled('status')) {
            $receptionsQuery->where('status', $request->status);
        }
        if ($request->filled('service_date')) {
            $receptionsQuery->whereDate('date', $request->service_date);
        }
        $receptions = $receptionsQuery->where('status', 'confirmed')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'type' => 'reception',
                'name' => $r->service?->name,
                'patient' => $r->patient?->last_name . ' ' . $r->patient?->first_name,
                'doctor' => $r->staff?->full_name,
                'price' => $r->service?->price,
                'currency' => $r->service?->currency ?? 'UZS',
                'date_time' => trim($r->date . ' ' . $r->start_time),
                'status' => '<span class="badge bg-label-info">'.$r->statusTranslate($r->status).'</span>',
            ];
        });


        // PatientProcedure
        $proceduresQuery = PatientProcedure::with(['procedure', 'patient']);
        if ($request->filled('first_name')) {
            $proceduresQuery->whereHas('patient', fn($q) => $q->where('first_name', 'like', '%'.$request->first_name.'%'));
        }
        if ($request->filled('last_name')) {
            $proceduresQuery->whereHas('patient', fn($q) => $q->where('last_name', 'like', '%'.$request->last_name.'%'));
        }
        if ($request->filled('service_date')) {
            $proceduresQuery->whereDate('procedure_date', $request->service_date);
        }

        $procedures = $proceduresQuery->where('status', 'confirmed')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'type' => 'procedure',
                'name' => $p->procedure?->name,
                'patient' => $p->patient?->last_name . ' ' . $p->patient?->first_name,
                'doctor' => '-', // если не привязан врач
                'price' => $p->procedure?->price,
                'currency' => 'UZS',
                'date_time' => $p->procedure_date,
                'status' => '<span class="badge bg-label-secondary">Процедура</span>',
            ];
        });

        // PatientMedication
        $MedicationQuery = PatientMedication::with(['medication', 'patient']);
        if ($request->filled('first_name')) {
            $MedicationQuery->whereHas('patient', fn($q) => $q->where('first_name', 'like', '%'.$request->first_name.'%'));
        }
        if ($request->filled('last_name')) {
            $MedicationQuery->whereHas('patient', fn($q) => $q->where('last_name', 'like', '%'.$request->last_name.'%'));
        }
        if ($request->filled('service_date')) {
            $MedicationQuery->whereDate('procedure_date', $request->service_date);
        }

        $medication = $MedicationQuery->where('status', 'sent_to_floor')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'type' => 'medication',
                'name' => $p->medication?->name,
                'patient' => $p->patient?->last_name . ' ' . $p->patient?->first_name,
                'doctor' => '-', // если не привязан врач
                'price' => $p->price ?? 0,
                'currency' => 'UZS',
                'date_time' => $p->created_at,
                'status' => '<span class="badge bg-label-secondary">Лист назначение</span>',
            ];
        });


        // Объединяем
        $all = collect()
            ->merge($services)
            ->merge($receptions)
            ->merge($procedures)
            ->merge($medication)
            ->sortByDesc('date_time');

        // Пагинация вручную (на Collection)
        $perPage = 15;
        $page = request()->get('page', 1);
        $items = $all->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $all->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );


        return view('admin.cashier.index', [
            'records' => $paginated
        ]);
    }

    public function updateAll(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
        ]);

        foreach ($request->records as $item) {
            [$type, $id] = explode(':', $item);

            switch ($type) {
                case 'service':
                    $model = \App\Models\PatientService::find($id);
                    if ($model) {
                        $model->status = 'paid';
                        $model->save();
                    }
                    break;

                case 'reception':
                    $model = \App\Models\Reception::find($id);
                    if ($model) {
                        $model->status = 'paid';
                        $model->save();
                    }
                    break;

                    case 'medication':
                    $model = \App\Models\PatientMedication::find($id);
                    if ($model) {
                        $model->status = 'received_at_station';
                        $model->save();
                    }
                    break;

                case 'procedure':
                    $model = \App\Models\Procedure::find($id);
                    if ($model) {
                        $model->status = 'paid';
                        $model->save();
                    }
                    break;

            }
        }

        return back()->with('success', 'Выбранные услуги успешно оплачены');
    }


    public function export(Request $request)
    {
        // Скопируй сборку данных из index()
        $services   = PatientService::with(['service', 'patient', 'doctor'])
            ->where('status', 'confirmed')->get()->map(fn($s) => [
                'id' => $s->id,
                'type' => 'service',
                'name' => $s->service?->name,
                'patient' => $s->patient?->last_name . ' ' . $s->patient?->first_name,
                'doctor' => $s->doctor?->full_name,
                'price' => $s->service?->price,
                'currency' => $s->service?->currency ?? 'UZS',
                'date_time' => trim($s->service_date . ' ' . $s->service_time),
                'status' => $s->getStatusBadge(),
            ]);

        $receptions = Reception::with(['service', 'patient', 'staff'])
            ->where('status', 'confirmed')->get()->map(fn($r) => [
                'id' => $r->id,
                'type' => 'reception',
                'name' => $r->service?->name,
                'patient' => $r->patient?->last_name . ' ' . $r->patient?->first_name,
                'doctor' => $r->staff?->full_name,
                'price' => $r->service?->price,
                'currency' => $r->service?->currency ?? 'UZS',
                'date_time' => trim($r->date . ' ' . $r->start_time),
                'status' => $r->statusTranslate($r->status),
            ]);

        $procedures = PatientProcedure::with(['procedure', 'patient'])
            ->where('status', 'confirmed')->get()->map(fn($p) => [
                'id' => $p->id,
                'type' => 'procedure',
                'name' => $p->procedure?->name,
                'patient' => $p->patient?->last_name . ' ' . $p->patient?->first_name,
                'doctor' => '-',
                'price' => $p->procedure?->price,
                'currency' => 'UZS',
                'date_time' => $p->procedure_date,
                'status' => 'Процедура',
            ]);

        $medication = PatientMedication::with(['medication', 'patient'])
            ->where('status', 'sent_to_floor')->get()->map(fn($p) => [
                'id' => $p->id,
                'type' => 'medication',
                'name' => $p->medication?->name,
                'patient' => $p->patient?->last_name . ' ' . $p->patient?->first_name,
                'doctor' => '-',
                'price' => $p->price ?? 0,
                'currency' => 'UZS',
                'date_time' => $p->created_at,
                'status' => 'Лист назначение',
            ]);

        $all = collect()
            ->merge($services)
            ->merge($receptions)
            ->merge($procedures)
            ->merge($medication)
            ->sortByDesc('date_time');

        $total = $all->sum('price');

        return Excel::download(new CashierStatsExport($all, $total), 'cashier_stats.xlsx');
    }

}
