<?php

namespace App\Services\Scheduling;

use InvalidArgumentException;

/**
 * A half-open span of the day, [start, end), in minutes since midnight.
 */
final readonly class TimeRange
{
    public function __construct(public int $start, public int $end)
    {
        if ($start < 0 || $end < $start) {
            throw new InvalidArgumentException("Invalid time range {$start}-{$end}.");
        }
    }

    /**
     * @param  string  $start  "H:i" or "H:i:s", as forms send it and PostgreSQL returns a time column
     * @param  string  $end  "H:i" or "H:i:s"
     */
    public static function fromTimes(string $start, string $end): self
    {
        return new self(self::minutesOf($start), self::minutesOf($end));
    }

    /**
     * The span of a stored row, or null when the row is unusable (missing times, end before start).
     */
    public static function tryFromTimes(?string $start, ?string $end): ?self
    {
        try {
            return $start === null || $end === null ? null : self::fromTimes($start, $end);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public static function minutesOf(string $time): int
    {
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $time, $parts) !== 1) {
            throw new InvalidArgumentException("Invalid time of day '{$time}'.");
        }

        return (int) $parts[1] * 60 + (int) $parts[2];
    }

    /**
     * Whether the two spans share at least one minute; touching ends do not overlap.
     */
    public function overlaps(self $other): bool
    {
        return $this->start < $other->end && $other->start < $this->end;
    }

    public function contains(self $other): bool
    {
        return $this->start <= $other->start && $other->end <= $this->end;
    }

    public function startTime(): string
    {
        return $this->format($this->start);
    }

    public function endTime(): string
    {
        return $this->format($this->end);
    }

    private function format(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
