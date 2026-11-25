<?php

namespace App\Enums;

use App\Enums\Concerns\HasLifecycle;
use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasBadge;

/**
 * Lifecycle of an outpatient appointment (receptions.status). The labels agree with «приём».
 */
enum ReceptionStatus: string implements HasBadge
{
    use HasLifecycle;
    use HasValues;

    case New = 'new';
    case Confirmed = 'confirmed';
    case Paid = 'paid';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::Confirmed => 'Подтверждён',
            self::Paid => 'Оплачен',
            self::Done => 'Выполнен',
            self::Cancelled => 'Отменён',
        };
    }

    /**
     * Statuses a booking may start with: the cash desk (accept_payments) may take the payment while
     * booking (ReceptionRequest).
     *
     * @return list<self>
     */
    public static function initial(): array
    {
        return [self::New, self::Paid];
    }

    /**
     * Registrar confirmation, payment at the cash desk and the visit itself move an appointment
     * forward. It can be cancelled until it is paid; afterwards that is a refund, which is not a
     * status change (as for ServiceStatus): a cancelled appointment would no longer be a record
     * of the payment.
     *
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::New => [self::Confirmed, self::Paid, self::Cancelled],
            self::Confirmed => [self::Paid, self::Cancelled],
            self::Paid => [self::Done],
            self::Done, self::Cancelled => [],
        };
    }

    /**
     * Paid at the cash desk, and the visit that followed: the appointment is the record of that payment.
     */
    public function isPaid(): bool
    {
        return in_array($this, [self::Paid, self::Done], true);
    }

    /**
     * Whether the appointment keeps its time in the doctor's calendar; a cancelled one frees it.
     */
    public function holdsSlot(): bool
    {
        return $this !== self::Cancelled;
    }

    /** The colours of ServiceStatus for the same steps: the cashier lists both side by side. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::New, self::Confirmed => 'bg-success-lt',
            self::Paid => 'bg-primary-lt',
            self::Done => 'bg-info-lt',
            self::Cancelled => 'bg-warning-lt',
        };
    }
}
