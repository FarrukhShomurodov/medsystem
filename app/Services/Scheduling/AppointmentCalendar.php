<?php

namespace App\Services\Scheduling;

use App\Enums\ReceptionStatus;
use App\Enums\ScheduleType;
use App\Models\DayOfWeek;
use App\Models\Reception;
use App\Models\Staff;
use App\Models\StaffAbsence;
use App\Models\StaffSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * The appointment calendar of one employee over a period of days, read with the same few
 * queries however many days it covers. Days are calendar dates of the app timezone.
 */
final readonly class AppointmentCalendar
{
    /**
     * @param  array<int, list<TimeRange>>  $receptionHours  keyed by day_of_week_id
     * @param  Collection<int, StaffAbsence>  $absences
     * @param  array<string, array<int, TimeRange>>  $appointments  keyed by date, then by reception id
     */
    private function __construct(
        private ?SlotGrid $grid,
        private array $receptionHours,
        private Collection $absences,
        private array $appointments,
    ) {}

    /**
     * Only employees who are employed, offer an appointment service and have an appointment
     * length take appointments. Without a length there is no grid, so the calendar is empty;
     * a dismissed employee, or one who no longer offers an appointment service, has no free slot
     * but keeps the appointments booked before, so they stay in sight to be moved or cancelled.
     */
    public static function of(Staff $staff, CarbonInterface $from, CarbonInterface $to): self
    {
        $minutes = $staff->slotMinutes();

        if ($minutes === null) {
            return new self(null, [], new Collection, []);
        }

        $takesAppointments = ! $staff->isDismissed()
            && $staff->services()->where('is_appointment', true)->exists();

        return new self(
            new SlotGrid($minutes),
            $takesAppointments ? self::receptionHoursOf($staff) : [],
            $takesAppointments ? $staff->absence()->overlapping($from, $to)->get() : new Collection,
            self::appointmentsOf($staff, $from, $to),
        );
    }

    /**
     * Every slot of the day; the ones inside the reception hours are available. Empty on days
     * without reception hours and on days the employee is absent.
     *
     * @return list<array{time: string, is_available: bool, admission_period: int}>
     */
    public function availability(CarbonInterface $date): array
    {
        $hours = $this->receptionHours[DayOfWeek::idOf($date)] ?? [];

        if ($this->grid === null || $hours === [] || $this->isAbsentOn($date)) {
            return [];
        }

        return $this->grid->availability($hours);
    }

    /**
     * Every slot of the day with the appointment that overlaps it; empty when nothing is booked.
     *
     * @return list<array{time: string, admission_period: int, is_booked: bool, reception_id: int|null}>
     */
    public function bookings(CarbonInterface $date): array
    {
        $appointments = $this->appointments[$date->toDateString()] ?? [];

        if ($this->grid === null || $appointments === []) {
            return [];
        }

        return $this->grid->bookings($appointments);
    }

    /**
     * The slots of every day of the period, as the doctor's weekly calendar draws them. A day
     * without free slots (an absence, no reception hours) still has the slots of the appointments
     * booked on it.
     *
     * @return list<array{date: string, time: string, is_available: bool, is_booked: bool, reception_id: int|null}>
     */
    public function days(CarbonInterface $from, CarbonInterface $to): array
    {
        $days = [];

        for ($date = CarbonImmutable::instance($from)->startOfDay(); $date->lte($to); $date = $date->addDay()) {
            $bookings = array_column($this->bookings($date), null, 'time');
            $slots = $this->availability($date)
                ?: array_values(array_map(
                    fn (array $booking): array => ['time' => $booking['time'], 'is_available' => false],
                    array_filter($bookings, fn (array $booking): bool => $booking['is_booked']),
                ));

            foreach ($slots as $slot) {
                $days[] = [
                    'date' => $date->toDateString(),
                    'time' => $slot['time'],
                    'is_available' => $slot['is_available'],
                    'is_booked' => $bookings[$slot['time']]['is_booked'] ?? false,
                    'reception_id' => $bookings[$slot['time']]['reception_id'] ?? null,
                ];
            }
        }

        return $days;
    }

    /**
     * Whether an appointment at the time would be inside the reception hours of the date's weekday,
     * on a day the employee is not absent: the reception-hours and absence rules of a booking
     * (ReceptionService::ensureBookable()), not its grid. An employee who takes no appointments
     * covers no time.
     * Absences are known for the period the calendar was read for.
     */
    public function covers(CarbonInterface $date, TimeRange $time): bool
    {
        $hours = $this->receptionHours[DayOfWeek::idOf($date)] ?? [];

        return ! $this->isAbsentOn($date)
            && collect($hours)->contains(fn (TimeRange $range): bool => $range->contains($time));
    }

    private function isAbsentOn(CarbonInterface $date): bool
    {
        return $this->absences->contains(fn (StaffAbsence $absence): bool => $absence->covers($date));
    }

    /**
     * @return array<int, list<TimeRange>>
     */
    private static function receptionHoursOf(Staff $staff): array
    {
        return $staff->schedule()
            ->where('type', ScheduleType::Reception)
            ->get(['day_of_week_id', 'start_time', 'end_time'])
            ->groupBy('day_of_week_id')
            ->map(fn (Collection $rows): array => array_values($rows
                ->map(fn (StaffSchedule $row): ?TimeRange => TimeRange::tryFromTimes($row->start_time, $row->end_time))
                ->filter()
                ->all()))
            ->all();
    }

    /**
     * @return array<string, array<int, TimeRange>>
     */
    private static function appointmentsOf(Staff $staff, CarbonInterface $from, CarbonInterface $to): array
    {
        return $staff->receptions()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where('status', '!=', ReceptionStatus::Cancelled)
            ->orderBy('start_time')
            ->get(['id', 'date', 'start_time', 'end_time'])
            ->groupBy('date')
            ->map(fn (Collection $rows): array => $rows
                ->mapWithKeys(fn (Reception $reception): array => [
                    $reception->id => TimeRange::tryFromTimes($reception->start_time, $reception->end_time),
                ])
                ->filter()
                ->all())
            ->all();
    }
}
