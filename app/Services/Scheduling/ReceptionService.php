<?php

namespace App\Services\Scheduling;

use App\Enums\ReceptionStatus;
use App\Enums\ScheduleType;
use App\Exceptions\Scheduling\ReceptionException;
use App\Models\DayOfWeek;
use App\Models\ExaminationMap;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ReceptionService
{
    /** SQLSTATE of the receptions_no_overlap exclusion constraint. */
    private const string EXCLUSION_VIOLATION = '23P01';

    /**
     * Books an appointment unless the employee is dismissed, takes no appointments for the service,
     * is absent that day, the time is not one slot of their grid (SlotGrid), lies outside their
     * reception hours or overlaps an appointment they already have. Concurrent bookings of one
     * employee are serialised by a lock on their staff row, so two registrars cannot take the same
     * time. The appointment keeps the service's current price and its currency: the cash desk
     * charges those.
     *
     * @param  array{patient_id: int, staff_id: int, service_id: int, date: string, start_time: string, end_time: string, comment?: string|null, status?: string|null}  $validated
     *
     * @throws ReceptionException
     */
    public function book(array $validated): Reception
    {
        $date = CarbonImmutable::parse($validated['date']);
        $status = ReceptionStatus::from($validated['status'] ?? ReceptionStatus::New->value);
        $time = TimeRange::fromTimes($validated['start_time'], $validated['end_time']);

        try {
            return DB::transaction(function () use ($validated, $date, $time, $status): Reception {
                $staff = Staff::query()->lockForUpdate()->findOrFail($validated['staff_id']);

                $this->ensureBookable($staff, (int) $validated['service_id'], $date, $time);
                $service = Service::query()->findOrFail($validated['service_id']);

                return Reception::query()->create([
                    ...Arr::only($validated, ['patient_id', 'staff_id', 'service_id', 'comment']),
                    // An appointment of an inpatient belongs to their current stay.
                    'examination_map_id' => ExaminationMap::query()->active()->where('patient_id', $validated['patient_id'])->value('id'),
                    'date' => $date->toDateString(),
                    'start_time' => $time->startTime(),
                    'end_time' => $time->endTime(),
                    'status' => $status,
                    'price' => $service->price,
                    'currency' => $service->priceCurrency(),
                    // Booked at the cash desk and paid in one go (ReceptionStatus::initial()).
                    'paid_at' => $status === ReceptionStatus::Paid ? now() : null,
                ]);
            });
        } catch (QueryException $e) {
            // The database constraint caught an overlap written past the lock (another code path).
            if ($e->getCode() === self::EXCLUSION_VIOLATION) {
                throw ReceptionException::slotTaken();
            }

            throw $e;
        }
    }

    /**
     * Moves the appointment to the status if its lifecycle allows it. Asking for the status it
     * already has changes nothing, so a repeated click is harmless.
     *
     * @throws ReceptionException
     */
    public function changeStatus(Reception $reception, ReceptionStatus $status): Reception
    {
        return DB::transaction(function () use ($reception, $status): Reception {
            $reception = Reception::query()->lockForUpdate()->findOrFail($reception->id);
            $current = $reception->status;

            if ($current === $status) {
                return $reception;
            }

            if (! $current->canTransitionTo($status)) {
                throw ReceptionException::transitionNotAllowed($current, $status);
            }

            $reception->update(['status' => $status]);

            return $reception;
        });
    }

    /**
     * The employee's appointments from today on, not yet done or cancelled, that their calendar no
     * longer covers: on a day of absence, outside the reception hours of their weekday, for a service
     * the employee no longer offers, or kept by a dismissed employee. One booked on an older grid (of
     * another appointment length) is not listed: only the grid moved, and it still blocks the slots
     * it overlaps. Changing the staff form does not cancel them (an absence is a fact to record, and
     * a paid appointment cannot be cancelled): the registrars move or cancel them. Read it after the
     * change is committed; StaffService locks the staff row as a booking does, so no booking slips
     * in unseen.
     *
     * @return Collection<int, Reception>
     */
    public function uncoveredAppointmentsOf(Staff $staff): Collection
    {
        $today = CarbonImmutable::today();

        $appointments = $staff->receptions()
            ->where('date', '>=', $today->toDateString())
            ->whereNotIn('status', ReceptionStatus::finalStatuses())
            ->with(['patient:id,first_name,last_name,middle_name', 'map:id,patient_id', 'map.patient:id,first_name,last_name,middle_name'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        if ($appointments->isEmpty()) {
            return $appointments;
        }

        $calendar = AppointmentCalendar::of($staff, $today, CarbonImmutable::parse((string) $appointments->max('date')));
        $offered = $staff->services()->where('is_appointment', true)->pluck('services.id');

        return $appointments
            ->reject(function (Reception $reception) use ($calendar, $offered): bool {
                $time = TimeRange::tryFromTimes($reception->start_time, $reception->end_time);

                return $time !== null
                    && $offered->contains($reception->service_id)
                    && $calendar->covers(CarbonImmutable::parse($reception->date), $time);
            })
            ->values();
    }

    /**
     * @throws ReceptionException
     */
    private function ensureBookable(Staff $staff, int $serviceId, CarbonImmutable $date, TimeRange $time): void
    {
        if ($staff->isDismissed()) {
            throw ReceptionException::staffDismissed();
        }

        // The calendars only offer employees with an appointment length and their appointment
        // services on the price list; a crafted request must not book anyone else or anything else.
        $minutes = $staff->slotMinutes();

        if ($minutes === null || ! $staff->services()->whereKey($serviceId)->where('is_appointment', true)->where('is_active', true)->exists()) {
            throw ReceptionException::serviceNotOffered();
        }

        // The calendars book only the slots of the employee's current grid. The end time the browser
        // sends is not trusted, or a single booking could take the doctor's whole day; nor is a start
        // between two slots, which would leave gaps no calendar can offer. Appointments booked on an
        // older grid stay as they are and still block the slots they overlap.
        if ($time->end - $time->start !== $minutes) {
            throw ReceptionException::lengthMismatch($minutes);
        }

        if (! (new SlotGrid($minutes))->has($time)) {
            throw ReceptionException::offGrid($minutes);
        }

        if (! $this->withinReceptionHours($staff, $date, $time)) {
            throw ReceptionException::outsideReceptionHours();
        }

        if ($staff->absence()->covering($date)->exists()) {
            throw ReceptionException::staffAbsent();
        }

        $taken = $staff->receptions()
            ->where('date', $date->toDateString())
            ->where('status', '!=', ReceptionStatus::Cancelled)
            ->where('start_time', '<', $time->endTime())
            ->where('end_time', '>', $time->startTime())
            ->exists();

        if ($taken) {
            throw ReceptionException::slotTaken();
        }
    }

    private function withinReceptionHours(Staff $staff, CarbonImmutable $date, TimeRange $time): bool
    {
        return $staff->schedule()
            ->where('type', ScheduleType::Reception)
            ->where('day_of_week_id', DayOfWeek::idOf($date))
            ->get(['start_time', 'end_time'])
            ->contains(fn (StaffSchedule $hours): bool => TimeRange::tryFromTimes($hours->start_time, $hours->end_time)?->contains($time) ?? false);
    }
}
