<?php

namespace App\Queries\Patients;

use App\Models\ExaminationMap;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The visit history of the patient card: every record of the patient, newest first. A hospital
 * stay is shown by its admission and discharge rows, followed by the records made during it.
 *
 * @phpstan-type TimelineEvent array{type: string, date: Carbon, data: Model, is_main: bool, parent_id: int|null}
 */
class PatientTimeline
{
    /**
     * Relation of the patient (or of a stay) => type of the history row (and of its partial view).
     * The relations of the patient hold the records of their stays too; the history lists those
     * under their stay only.
     */
    private const array RECORDS = [
        'assignments' => 'assignment',
        'services' => 'service',
        'diagnoses' => 'diagnosis',
        'receptions' => 'reception',
        'surveyResponses' => 'patient_med_document',
        'patientProcedures' => 'procedure',
        'patientMedications' => 'medication',
        'documentResponses' => 'document',
    ];

    /** Records a stay does not have: referrals are made outside a hospital stay. */
    private const array PATIENT_ONLY = ['assignments'];

    /**
     * @return Collection<int, TimelineEvent>
     */
    public function for(Patient $patient): Collection
    {
        $this->load($patient);

        $events = $this->recordsOf($patient, null);

        foreach ($patient->examinationMaps as $map) {
            $events->push($this->event('examination_map_start', $map->admission_date, $map));

            if ($map->discharge_date) {
                $events->push($this->event('examination_map_end', $map->discharge_date, $map));
            }

            $events = $events->concat($this->recordsOf($map, $map->id));
        }

        return $events->sortByDesc(fn (array $event): int => $event['date']->getTimestamp())->values();
    }

    private function load(Patient $patient): void
    {
        $withoutStay = fn (HasMany $query) => $query->whereNull('examination_map_id');

        $patient->load([
            'examinationMaps' => fn (HasMany $query) => $query->with([
                'services.service:id,name',
                'diagnoses.diagnosis:id,code,name',
                'receptions',
                'surveyResponses',
                'patientProcedures',
                'patientMedications',
                'documentResponses',
            ]),
            'assignments',
            // The patient's records outside any stay; the history row of a service names it, the one
            // of a diagnosis gives its code.
            'services' => $withoutStay,
            'services.service:id,name',
            'diagnoses' => $withoutStay,
            'diagnoses.diagnosis:id,code,name',
            'receptions' => $withoutStay,
            'surveyResponses' => $withoutStay,
            'patientProcedures' => $withoutStay,
            'patientMedications' => $withoutStay,
            'documentResponses' => $withoutStay,
        ]);
    }

    /**
     * The history rows of the loaded records of a patient (outside any stay) or of one stay.
     *
     * @return Collection<int, TimelineEvent>
     */
    private function recordsOf(Patient|ExaminationMap $owner, ?int $stayId): Collection
    {
        $relations = $owner instanceof ExaminationMap
            ? array_diff_key(self::RECORDS, array_flip(self::PATIENT_ONLY))
            : self::RECORDS;

        return collect($relations)->flatMap(fn (string $type, string $relation): Collection => $owner
            ->getRelation($relation)
            ->map(fn (Model $record): array => $this->event($type, $record->getAttribute('created_at'), $record, $stayId)));
    }

    /**
     * @return TimelineEvent
     */
    private function event(string $type, mixed $date, Model $record, ?int $stayId = null): array
    {
        return [
            'type' => $type,
            'date' => Carbon::parse($date),
            'data' => $record,
            'is_main' => $stayId === null,
            'parent_id' => $stayId,
        ];
    }
}
