<?php

namespace App\Queries\Patients;

use App\Models\Admin;
use App\Models\AdminSelectedDiagnosis;
use App\Models\Diagnosis;
use App\Queries\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * The diagnosis picker of the patient card. The card renders the viewer's favourites; the rest of
 * the ICD-10 catalogue (some 14 000 codes once the ministry's sheet is imported) is searched here as
 * the user types, at most LIMIT diagnoses at a time.
 */
final class DiagnosisPickerQuery
{
    public const int LIMIT = 50;

    /**
     * The viewer's favourite diagnoses, by code.
     *
     * @return Collection<int, Diagnosis>
     */
    public function favorites(Admin $viewer): Collection
    {
        return Diagnosis::query()
            ->whereIn('id', $this->favoriteIds($viewer))
            ->orderBy('code')
            ->orderBy('id')
            ->get(['id', 'code', 'name']);
    }

    /**
     * The other diagnoses whose code starts with the typed text, or whose name holds every typed
     * word; none before anything was typed.
     *
     * @return Collection<int, Diagnosis>
     */
    public function search(Admin $viewer, string $term): Collection
    {
        $term = trim($term);
        $words = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return new Collection;
        }

        return Diagnosis::query()
            ->select(['id', 'code', 'name'])
            ->whereNotIn('id', $this->favoriteIds($viewer))
            ->where(fn (Builder $query) => $query
                ->whereLike('code', LikePattern::startsWith($term))
                ->orWhere(function (Builder $name) use ($words): void {
                    foreach ($words as $word) {
                        $name->whereLike('name', LikePattern::contains($word));
                    }
                }))
            ->orderBy('code')
            ->orderBy('id')
            ->limit(self::LIMIT)
            ->get();
    }

    /**
     * @return Builder<AdminSelectedDiagnosis>
     */
    private function favoriteIds(Admin $viewer): Builder
    {
        return AdminSelectedDiagnosis::query()->where('admin_id', $viewer->id)->select('diagnosis_id');
    }
}
