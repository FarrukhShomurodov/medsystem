<?php

namespace App\Enums\Concerns;

/**
 * The transition rules of a status lifecycle, derived from the one list each status enum
 * declares: nextStatuses(). A status without successors is final.
 */
trait HasLifecycle
{
    /**
     * The statuses this one may move to, in the order the UI offers them.
     *
     * @return list<self>
     */
    abstract public function nextStatuses(): array;

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }

    /** Nothing leaves a final status: the record can no longer change its course. */
    public function isFinal(): bool
    {
        return $this->nextStatuses() === [];
    }

    /**
     * @return list<self>
     */
    public static function finalStatuses(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status): bool => $status->isFinal()));
    }

    /**
     * The values of the final statuses, for queries over the records still in progress.
     *
     * @return list<string>
     */
    public static function finalValues(): array
    {
        return array_column(self::finalStatuses(), 'value');
    }
}
