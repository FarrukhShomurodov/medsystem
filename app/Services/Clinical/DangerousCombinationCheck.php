<?php

namespace App\Services\Clinical;

use App\Enums\PatientMedicationStatus;
use App\Models\DangerousCombination;
use App\Models\Patient;
use App\Models\PatientMedication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Finds the known dangerous combinations (dangerous_combinations, imported by name) among a
 * patient's prescriptions. The doctor is warned, not stopped: some combinations are allowed on
 * strict indications, and the decision stays clinical.
 */
final class DangerousCombinationCheck
{
    /**
     * The combinations of the given prescriptions with each other and with the patient's other
     * prescriptions that are still in progress.
     *
     * @param  Collection<int, PatientMedication>  $prescriptions  of one patient
     * @return Collection<int, DangerousCombination>
     */
    public function forPrescriptions(Collection $prescriptions): Collection
    {
        $first = $prescriptions->first();

        if ($first === null) {
            return new Collection;
        }

        $prescriptions->loadMissing('medication');
        $inProgress = $this->inProgress((int) $first->patient_id);

        return $this->among($this->names($prescriptions), $this->names($inProgress->concat($prescriptions)));
    }

    /**
     * The combinations among the patient's prescriptions still in progress: cancelled, returned
     * and completed ones no longer interact.
     *
     * @return Collection<int, DangerousCombination>
     */
    public function forPatient(Patient $patient): Collection
    {
        $names = $this->names($this->inProgress($patient->id));

        return $this->among($names, $names);
    }

    /**
     * @return Collection<int, PatientMedication>
     */
    private function inProgress(int $patientId): Collection
    {
        return PatientMedication::query()
            ->with('medication')
            ->where('patient_id', $patientId)
            ->whereNotIn('status', PatientMedicationStatus::finalValues())
            ->get();
    }

    /**
     * The combinations of a drug of the first list with a drug of the second.
     *
     * @param  BaseCollection<int, non-falsy-string>  $new  normalized
     * @param  BaseCollection<int, non-falsy-string>  $all  normalized
     * @return Collection<int, DangerousCombination>
     */
    private function among(BaseCollection $new, BaseCollection $all): Collection
    {
        if ($new->isEmpty()) {
            return new Collection;
        }

        // Names are imported as typed; the search widens to the usual spellings and the exact
        // match is done here, independent of the database collation.
        $candidates = DangerousCombination::query()
            ->where(fn ($query) => $query
                ->whereIn('drug1_ru', $this->spellings($new))->whereIn('drug2_ru', $this->spellings($all)))
            ->orWhere(fn ($query) => $query
                ->whereIn('drug2_ru', $this->spellings($new))->whereIn('drug1_ru', $this->spellings($all)))
            ->get();

        return $candidates
            ->filter(function (DangerousCombination $combination) use ($new, $all): bool {
                $first = $this->normalize($combination->drug1_ru);
                $second = $this->normalize($combination->drug2_ru);

                return ($new->contains($first) && $all->contains($second))
                    || ($new->contains($second) && $all->contains($first));
            })
            ->values();
    }

    /**
     * The warning shown to the doctor.
     *
     * @param  Collection<int, DangerousCombination>  $combinations
     */
    public function warning(Collection $combinations): string
    {
        $lines = $combinations->map(fn (DangerousCombination $combination): string => trim(sprintf(
            '%s + %s%s%s',
            $combination->drug1_ru,
            $combination->drug2_ru,
            $combination->severity_ru ? " ({$combination->severity_ru})" : '',
            $combination->comment_ru ? ": {$combination->comment_ru}" : '',
        )));

        return 'Внимание, опасное сочетание препаратов. '.$lines->implode(' ');
    }

    /**
     * @param  Collection<int, PatientMedication>  $prescriptions
     * @return BaseCollection<int, non-falsy-string>
     */
    private function names(Collection $prescriptions): BaseCollection
    {
        return $prescriptions
            ->map(fn (PatientMedication $prescription): string => $this->normalize((string) $prescription->medication?->name))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * The ways a name is usually typed: "ацетилсалициловая кислота", "Ацетилсалициловая кислота",
     * "Ацетилсалициловая Кислота" and "АЦЕТИЛСАЛИЦИЛОВАЯ КИСЛОТА".
     *
     * @param  BaseCollection<int, non-falsy-string>  $names  normalized
     * @return list<string>
     */
    private function spellings(BaseCollection $names): array
    {
        return array_values($names
            ->flatMap(fn (string $name): array => [
                $name,
                mb_strtoupper(mb_substr($name, 0, 1)).mb_substr($name, 1),
                mb_convert_case($name, MB_CASE_TITLE),
                mb_strtoupper($name),
            ])
            ->unique()
            ->all());
    }

    private function normalize(?string $name): string
    {
        return mb_strtolower(trim((string) $name));
    }
}
