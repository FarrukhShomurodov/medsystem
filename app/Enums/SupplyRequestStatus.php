<?php

namespace App\Enums;

use App\Enums\Concerns\HasLifecycle;
use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasBadge;

/**
 * Lifecycle of a department's supply request to the main warehouse
 * (supply_requests.status).
 */
enum SupplyRequestStatus: string implements HasBadge
{
    use HasLifecycle;
    use HasValues;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В ожидании',
            self::Approved => 'Принят',
            self::Rejected => 'Отклонён',
        };
    }

    /**
     * A request is decided once, by the main warehouse: approving moves the stock, rejecting moves
     * nothing. Either way the department sends a new request for whatever it still needs.
     *
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected],
            self::Approved, self::Rejected => [],
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning-lt',
            self::Approved => 'bg-success-lt',
            self::Rejected => 'bg-secondary-lt',
        };
    }
}
