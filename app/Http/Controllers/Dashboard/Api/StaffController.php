<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduling\AppointmentWeekRequest;
use App\Models\Staff;
use App\Services\Scheduling\AppointmentCalendar;
use Illuminate\Http\JsonResponse;

/**
 * The slots of an employee's calendar. The routes (routes/ajax.php) check the permission of the
 * calling page and ReceptionPolicy: a doctor's account reads only its own.
 */
class StaffController extends Controller
{
    public function availabilityToday(Staff $staff): JsonResponse
    {
        return response()->json($staff->availabilityOn(today()));
    }

    public function bookedToday(Staff $staff): JsonResponse
    {
        return response()->json($staff->bookingsOn(today()));
    }

    public function availabilityWeek(AppointmentWeekRequest $request, Staff $staff): JsonResponse
    {
        $start = $request->start();
        $end = $start->addDays(6);

        return response()->json(AppointmentCalendar::of($staff, $start, $end)->days($start, $end));
    }
}
