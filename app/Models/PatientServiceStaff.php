<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Staff members assigned to perform a patient service.
 */
class PatientServiceStaff extends Pivot
{
    protected $table = 'patient_service_to_staffs_assignment';

    /** The pivot table has its own surrogate key. */
    public $incrementing = true;
}
