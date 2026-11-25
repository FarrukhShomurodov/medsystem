<?php

namespace App\Queries\Dashboard;

use App\Enums\ReceptionStatus;

/**
 * The appointments of the day by status, of the whole hospital or of one doctor.
 */
final readonly class ReceptionsToday
{
    /**
     * @param  array<string, int>  $byStatus  ReceptionStatus value => appointments
     * @param  bool  $ownOnly  the viewer is a doctor: the counts are of their own appointments
     */
    public function __construct(
        private array $byStatus,
        public bool $ownOnly,
    ) {}

    public function count(ReceptionStatus $status): int
    {
        return $this->byStatus[$status->value] ?? 0;
    }

    /** The booked appointments: all but the cancelled ones. */
    public function booked(): int
    {
        return array_sum($this->byStatus) - $this->count(ReceptionStatus::Cancelled);
    }

    /**
     * The statuses the day has appointments in, in lifecycle order.
     *
     * @return list<ReceptionStatus>
     */
    public function statuses(): array
    {
        return array_values(array_filter(ReceptionStatus::cases(), fn (ReceptionStatus $status): bool => $this->count($status) > 0));
    }
}
