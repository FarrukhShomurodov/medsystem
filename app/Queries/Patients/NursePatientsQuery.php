<?php

namespace App\Queries\Patients;

use App\Models\Admin;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\StaffProfessionalData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * The patients of the nurse window: inpatients with an open stay. An employee sees the stays of
 * the department they work in; an account that is not an employee of a department (an
 * administrator) sees every department.
 */
class NursePatientsQuery
{
    private const int PER_PAGE = 10;

    private function __construct(private readonly ?int $departmentId) {}

    public static function for(Admin $admin): self
    {
        $departmentId = $admin->staff_id === null ? null : StaffProfessionalData::query()
            ->where('staff_id', $admin->staff_id)
            ->value('department_id');

        return new self($departmentId === null ? null : (int) $departmentId);
    }

    /**
     * @return LengthAwarePaginator<int, Patient>
     */
    public function paginate(): LengthAwarePaginator
    {
        return $this->patients()
            ->select(['id', 'first_name', 'last_name', 'middle_name', 'birth_date'])
            ->with('activeExaminationMap:id,patient_id,department_id,admission_date,discharge_date')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /** How many patients the list holds. */
    public function count(): int
    {
        return $this->patients()->count();
    }

    /** Whether the list covers every department (the account works in none). */
    public function coversAllDepartments(): bool
    {
        return $this->departmentId === null;
    }

    /**
     * The open stay of the patient, if the nurse window of this account shows it.
     */
    public function activeMapOf(Patient $patient): ?ExaminationMap
    {
        return $this->inWard($patient->activeExaminationMap()->getQuery())->first();
    }

    /**
     * @return Builder<Patient>
     */
    private function patients(): Builder
    {
        return Patient::query()->whereHas('activeExaminationMap', fn (Builder $map): Builder => $this->inWard($map));
    }

    /**
     * @param  Builder<ExaminationMap>  $maps
     * @return Builder<ExaminationMap>
     */
    private function inWard(Builder $maps): Builder
    {
        return $maps->when($this->departmentId, fn (Builder $query, int $departmentId) => $query->where('department_id', $departmentId));
    }
}
