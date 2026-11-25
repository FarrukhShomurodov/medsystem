<?php

namespace App\Queries\Patients;

use App\Models\Patient;
use App\Queries\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * The patient picker of the booking dialogs and of the patient service form: the patients whose
 * name holds every typed word, at most LIMIT of them, so a page never downloads the whole register.
 */
final class PatientPickerQuery
{
    public const int LIMIT = 20;

    /**
     * @return Collection<int, Patient>
     */
    public function search(string $term): Collection
    {
        $words = preg_split('/\s+/u', trim($term), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return Patient::query()
            ->select(['id', 'first_name', 'last_name', 'middle_name', 'birth_date'])
            ->where(function (Builder $query) use ($words): void {
                foreach ($words as $word) {
                    $pattern = LikePattern::contains($word);

                    $query->where(fn (Builder $name) => $name
                        ->whereLike('last_name', $pattern)
                        ->orWhereLike('first_name', $pattern)
                        ->orWhereLike('middle_name', $pattern));
                }
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->limit(self::LIMIT)
            ->get();
    }
}
