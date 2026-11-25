<?php

namespace App\Enums;

use App\Enums\Concerns\HasLifecycle;
use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasBadge;

/**
 * Lifecycle of a stock transfer between warehouses (transfers.status).
 * The value 'canceled' is spelled as in the database CHECK constraint.
 */
enum TransferStatus: string implements HasBadge
{
    use HasLifecycle;
    use HasValues;

    case Pending = 'pending';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В ожидании',
            self::Completed => 'Завершён',
            self::Canceled => 'Отменён',
        };
    }

    /**
     * Stock moves exactly once, on pending -> completed; completed and canceled transfers are final.
     *
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Pending => [self::Completed, self::Canceled],
            self::Completed, self::Canceled => [],
        };
    }

    /** Only a pending transfer may still be edited or deleted: nothing has moved yet. */
    public function isEditable(): bool
    {
        return $this === self::Pending;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning-lt',
            self::Completed => 'bg-success-lt',
            self::Canceled => 'bg-secondary-lt',
        };
    }
}
