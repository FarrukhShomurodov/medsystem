<?php

namespace App\Queries\Patients;

use App\Models\ExaminationMap;
use App\Models\ExaminationMapVitalParameter;
use App\Models\VitalParameter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LogicException;
use stdClass;

/**
 * The vital signs measured during a stay, one row per measurement time, for the charts and the
 * table of the nurse window.
 */
class VitalSignsChart
{
    /** Chart series of the seeded vital parameters (VitalParameterSeeder names). */
    private const array SERIES = [
        'Систолическое АД' => 'systolic',
        'Диастолическое АД' => 'diastolic',
        'Пульс' => 'pulse',
        'Температура' => 'temperature',
        'Сатурация (SPO₂)' => 'saturation',
        'Частота дыхания (ЧДД)' => 'breathing_rate',
        'Уровень сознания' => 'consciousness',
        'Кислород/воздух' => 'oxygen_type',
        'Диурез / Баланс' => 'urine_balance',
    ];

    /** Columns of the measurement table, in display order. */
    private const array COLUMNS = [
        'systolic', 'diastolic', 'pulse', 'temperature', 'saturation', 'breathing_rate',
        'consciousness', 'oxygen_type', 'urine_balance', 'total_score',
    ];

    /**
     * @param  Collection<int, VitalParameter>  $parameters
     * @return array{records: \Illuminate\Support\Collection<int, stdClass>, jsChartData: array<int, array<string, float|int|string>>}
     */
    public function for(ExaminationMap $map, Collection $parameters): array
    {
        $series = $parameters->mapWithKeys(fn (VitalParameter $parameter): array => [
            $parameter->id => self::SERIES[$parameter->name] ?? Str::snake($parameter->name),
        ]);

        $chartData = ExaminationMapVitalParameter::query()
            ->where('examination_map_id', $map->id)
            ->orderBy('created_at')
            ->get(['vital_parameter_id', 'value', 'created_at'])
            ->groupBy(fn (ExaminationMapVitalParameter $measurement): string => $this->measuredAt($measurement))
            ->map(function (Collection $measurements, string $time) use ($series): array {
                $row = ['t' => $time];

                foreach ($measurements as $measurement) {
                    if ($key = $series[$measurement->vital_parameter_id] ?? null) {
                        $row[$key] = (float) $measurement->value;
                    }
                }

                return $row;
            })
            ->values()
            ->all();

        return [
            'records' => collect($chartData)->map($this->tableRow(...)),
            'jsChartData' => array_map(
                fn (array $row): array => ['t' => Carbon::parse($row['t'])->getTimestampMs()] + $row,
                $chartData,
            ),
        ];
    }

    /** The nurse window saves every measurement with its time (PatientParameterController). */
    private function measuredAt(ExaminationMapVitalParameter $measurement): string
    {
        $time = $measurement->created_at
            ?? throw new LogicException("Vital sign measurement {$measurement->id} has no time.");

        return $time->format('Y-m-d H:i:s');
    }

    /**
     * @param  array<string, float|string>  $row
     */
    private function tableRow(array $row): stdClass
    {
        $values = array_map(fn (string $column): float|string|null => $row[$column] ?? null, array_combine(self::COLUMNS, self::COLUMNS));

        return (object) ['datetime' => Carbon::parse($row['t'])->format('d.m.Y H:i'), ...$values];
    }
}
