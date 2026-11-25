<?php

namespace App\Services\Patients;

use App\Models\Admin;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the "recently viewed patients" list of an account: the opened card moves to the top and
 * only the most recent ones are kept.
 */
class RecordPatientView
{
    public const KEEP = 10;

    public function __invoke(Admin $admin, Patient $patient): void
    {
        $views = $admin->lastViewed();
        $now = now();

        DB::transaction(function () use ($admin, $patient, $views, $now): void {
            // A single statement on the unique (admin_id, patient_id) key: two cards opened at the
            // same moment update one row instead of both inserting the patient.
            $views->newPivotStatement()->upsert(
                [['admin_id' => $admin->id, 'patient_id' => $patient->id, 'created_at' => $now, 'updated_at' => $now]],
                ['admin_id', 'patient_id'],
                ['updated_at'],
            );

            $stale = $views->newPivotQuery()
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->offset(self::KEEP)
                ->pluck('id');

            if ($stale->isNotEmpty()) {
                $views->newPivotQuery()->whereIn('id', $stale)->delete();
            }
        });
    }
}
