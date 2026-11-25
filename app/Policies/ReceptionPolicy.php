<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Reception;
use App\Models\Staff;
use Illuminate\Auth\Access\Response;

/**
 * Whose appointment calendar an account works on, on top of the permissions of the booking routes:
 * a doctor's account (Admin::doctorProfile()) only on the doctor's own, every other account on any
 * employee's. It is checked wherever a request names an employee or one of their appointments: the
 * doctor's calendar page, the free and booked slots, a booking, the appointment dialog and its
 * cancellation; the doctors offered for a service are the calendars it allows.
 */
final class ReceptionPolicy
{
    /**
     * @param  Staff|Reception  $calendar  the employee, or an appointment on their calendar
     */
    public function workOnCalendar(Admin $actor, Staff|Reception $calendar): Response
    {
        $doctor = $actor->doctorProfile();
        $staffId = $calendar instanceof Reception ? $calendar->staff_id : $calendar->id;

        return $doctor === null || $doctor->id === $staffId
            ? Response::allow()
            : Response::deny('Врач работает только со своим календарём приёмов.');
    }
}
