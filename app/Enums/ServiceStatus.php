<?php

namespace App\Enums;

use App\Enums\Concerns\HasLifecycle;
use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasBadge;

/**
 * Lifecycle of a service or procedure ordered for a patient
 * (patient_services.status and patient_procedures.status). The labels agree with «услуга» and
 * «процедура», as the cashier prints them: "Услуга №12 — Оплачена".
 */
enum ServiceStatus: string implements HasBadge
{
    use HasLifecycle;
    use HasValues;

    case Assigned = 'assigned';
    case Confirmed = 'confirmed';
    case Paid = 'paid';
    case InProcess = 'in_process';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Назначена',
            self::Confirmed => 'Подтверждена',
            self::Paid => 'Оплачена',
            self::InProcess => 'В процессе',
            self::Completed => 'Завершена',
            self::Cancelled => 'Отменена',
        };
    }

    /**
     * The order is assigned, confirmed by the registrar, paid at the cashier, then carried out.
     * It can be cancelled until it is paid; afterwards that is a refund, which is not a status
     * change. Nothing leaves a completed or cancelled order, and nothing puts an order back into
     * the registrar's basket (assigned) once it moved on, so it is never billed twice.
     *
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Assigned => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Paid, self::Cancelled],
            self::Paid => [self::InProcess, self::Completed],
            self::InProcess => [self::Completed],
            self::Completed, self::Cancelled => [],
        };
    }

    /**
     * The next statuses a clinician may set on the patient card: payment is the cashier's.
     *
     * @return list<self>
     */
    public function manualNextStatuses(): array
    {
        return array_values(array_filter($this->nextStatuses(), fn (self $next): bool => $next !== self::Paid));
    }

    /**
     * Paid at the cashier, and every status after it: the order is the record of that payment.
     */
    public function isPaid(): bool
    {
        return in_array($this, [self::Paid, self::InProcess, self::Completed], true);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Assigned, self::Confirmed => 'bg-success-lt',
            self::Paid => 'bg-primary-lt',
            self::InProcess, self::Completed => 'bg-info-lt',
            self::Cancelled => 'bg-warning-lt',
        };
    }
}
