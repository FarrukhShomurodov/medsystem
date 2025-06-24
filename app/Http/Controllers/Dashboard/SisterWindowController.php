<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientVitalParameter;
use App\Models\Staff;
use App\Models\VitalParameter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

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
            $staff = Staff::with('professionalData.department.examinationMaps.patient')
                ->findOrFail($staffId);
            $examinationMaps = $staff->professionalData->department->examinationMaps;
            $patients = $examinationMaps->pluck('patient')->unique('id')->values();
        } else {
            $patients = Patient::whereHas('examinationMaps', fn($q) => $q->where('is_active', true))
                ->paginate(10);
        }

        $menu = [
            'general' => ['label' => 'Общие данные', 'graphic' => true],
            'breathing_rate' => ['label' => 'ЧДД', 'graphic' => false],
            'consciousness' => ['label' => 'Сознание', 'graphic' => false],
            'oxygen_type' => ['label' => 'Кислород/воздух', 'graphic' => false],
            'total_score' => ['label' => 'Общий балл', 'graphic' => false],
        ];

        // Загрузка параметров
        $vitalParameters = VitalParameter::all();

        // Создание карты ключей
        $paramKeys = $vitalParameters->mapWithKeys(fn($p) => [
            $p->id => match ($p->name) {
                'Систолическое АД' => 'systolic',
                'Диастолическое АД' => 'diastolic',
                'Пульс' => 'pulse',
                'Температура' => 'temperature',
                'Сатурация (SPO₂)' => 'saturation',
                'Частота дыхания (ЧДД)' => 'breathing_rate',
                'Уровень сознания' => 'consciousness',
                'Кислород/воздух' => 'oxygen_type',
                'Диурез / Баланс' => 'urine_balance',
                default => Str::snake($p->name),
            }
        ]);

        // Получение всех замеров для пациента
        $pvps = PatientVitalParameter::with('vitalParameter')
            ->where('patient_id', $patient->id)
            ->orderBy('created_at')
            ->get();

        // Группировка по времени
        $grouped = $pvps->groupBy(fn($p) => $p->created_at->format('Y-m-d H:i:s'));

        // Формирование данных для графиков
        $chartData = $grouped->map(function ($items, $timestamp) use ($paramKeys) {
            $row = ['t' => $timestamp];
            foreach ($items as $pivot) {
                $snake = $paramKeys[$pivot->vital_parameter_id] ?? null;
                if (!$snake) {
                    continue;
                }
                $row[$snake] = is_numeric($pivot->value) ? floatval($pivot->value) : $pivot->value;
            }
            return $row;
        })->values()->toArray();

        $records = collect($chartData)->map(fn($r) => (object)[
            'datetime' => \Carbon\Carbon::parse($r['t'])->format('d.m.Y H:i'),
            'systolic' => $r['systolic'] ?? null,
            'diastolic' => $r['diastolic'] ?? null,
            'pulse' => $r['pulse'] ?? null,
            'temperature' => $r['temperature'] ?? null,
            'saturation' => $r['saturation'] ?? null,
            'breathing_rate' => $r['breathing_rate'] ?? null,
            'consciousness' => $r['consciousness'] ?? null,
            'oxygen_type' => $r['oxygen_type'] ?? null,
            'urine_balance' => $r['urine_balance'] ?? null,
            'total_score' => $r['total_score'] ?? null,
        ]);

        // Преобразование времени для JS
        $jsChartData = collect($chartData)->map(function ($item) {
            $item['t'] = \Carbon\Carbon::parse($item['t'])->timestamp * 1000;
            return $item;
        })->toArray();

        return view(
            'admin.sister_window.show',
            compact(
                'patient',
                'patients',
                'menu',
                'vitalParameters',
                'chartData',
                'records',
                'jsChartData'
            )
        );
    }
}
