<?php

namespace App\Queries\Patients;

use App\Models\Patient;
use App\Queries\LikePattern;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * The patient list with the filters of its search form.
 */
class PatientListQuery
{
    private const int PER_PAGE = 10;

    /**
     * @param  array{id?: int|string|null, first_name?: string|null, second_name?: string|null, middle_name?: string|null, birth_date?: string|null, department_id?: int|string|null, staff_id?: int|string|null}  $filters
     * @return LengthAwarePaginator<int, Patient>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Patient::query()
            ->select(['id', 'first_name', 'last_name', 'middle_name', 'birth_date', 'gender'])
            ->with(['latestExaminationMap.department:id,name', 'latestExaminationMap.doctor:id,name,last_name,middle_name'])
            ->when($filters['id'] ?? null, fn (Builder $query, int|string $id) => $query->whereKey((int) $id))
            ->when($filters['first_name'] ?? null, fn (Builder $query, string $name) => $query->whereLike('first_name', LikePattern::contains(trim($name))))
            ->when($filters['second_name'] ?? null, fn (Builder $query, string $name) => $query->whereLike('last_name', LikePattern::contains(trim($name))))
            ->when($filters['middle_name'] ?? null, fn (Builder $query, string $name) => $query->whereLike('middle_name', LikePattern::contains(trim($name))))
            ->when($filters['birth_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('birth_date', $date))
            ->when($filters['department_id'] ?? null, fn (Builder $query, int|string $id) => $query
                ->whereHas('activeExaminationMap', fn (Builder $map) => $map->where('department_id', $id)))
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $id) => $query
                ->whereHas('activeExaminationMap', fn (Builder $map) => $map->where('staff_id', $id)))
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }
}
