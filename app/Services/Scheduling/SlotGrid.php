<?php

namespace App\Services\Scheduling;

/**
 * The appointment slots of one day: back-to-back spans of the doctor's appointment length
 * starting at midnight. The booking calendars draw one row per slot.
 */
final readonly class SlotGrid
{
    /** Bounds for the appointment length entered on the staff form (StaffRequest). */
    public const int MIN_MINUTES = 5;

    public const int MAX_MINUTES = 240;

    private const MINUTES_PER_DAY = 24 * 60;

    /** @var list<TimeRange> */
    private array $slots;

    public function __construct(public int $minutes)
    {
        $slots = [];

        // A non-positive length (legacy data) would never reach the end of the day.
        for ($start = 0; $minutes > 0 && $start < self::MINUTES_PER_DAY; $start += $minutes) {
            $slots[] = new TimeRange($start, $start + $minutes);
        }

        $this->slots = $slots;
    }

    /**
     * @return list<TimeRange>
     */
    public function slots(): array
    {
        return $this->slots;
    }

    /**
     * Whether the span is one of the grid's slots: one length long and starting a whole number of
     * lengths after midnight, as the constructor lays them out.
     */
    public function has(TimeRange $range): bool
    {
        return $this->minutes > 0
            && $range->end - $range->start === $this->minutes
            && $range->start % $this->minutes === 0
            && $range->start < self::MINUTES_PER_DAY;
    }

    /**
     * Every slot, available when it lies completely inside one of the reception hours: a slot
     * that starts at the end of the hours, or runs past it, is not offered.
     *
     * @param  list<TimeRange>  $receptionHours
     * @return list<array{time: string, is_available: bool, admission_period: int}>
     */
    public function availability(array $receptionHours): array
    {
        return array_map(fn (TimeRange $slot): array => [
            'time' => $slot->startTime(),
            'is_available' => $this->firstKey($receptionHours, fn (TimeRange $hours): bool => $hours->contains($slot)) !== null,
            'admission_period' => $this->minutes,
        ], $this->slots);
    }

    /**
     * Every slot, booked when an appointment overlaps it, whatever grid the appointment was made on.
     *
     * @param  array<int, TimeRange>  $appointments  keyed by reception id
     * @return list<array{time: string, admission_period: int, is_booked: bool, reception_id: int|null}>
     */
    public function bookings(array $appointments): array
    {
        return array_map(function (TimeRange $slot) use ($appointments): array {
            $receptionId = $this->firstKey($appointments, fn (TimeRange $appointment): bool => $appointment->overlaps($slot));

            return [
                'time' => $slot->startTime(),
                'admission_period' => $this->minutes,
                'is_booked' => $receptionId !== null,
                'reception_id' => $receptionId,
            ];
        }, $this->slots);
    }

    /**
     * @template TKey of array-key
     *
     * @param  array<TKey, TimeRange>  $ranges
     * @param  callable(TimeRange): bool  $matches
     * @return TKey|null
     */
    private function firstKey(array $ranges, callable $matches): int|string|null
    {
        foreach ($ranges as $key => $range) {
            if ($matches($range)) {
                return $key;
            }
        }

        return null;
    }
}
