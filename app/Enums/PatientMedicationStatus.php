<?php

namespace App\Enums;

use App\Enums\Concerns\HasLifecycle;
use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasBadge;
use App\Enums\Contracts\HasIcon;

/**
 * Lifecycle of a prescribed medication (patient_medications.status):
 * ordered by the doctor, sent from the pharmacy to the ward, received by
 * the nurse station, then given to the patient or returned / cancelled. The labels agree with
 * «лист назначения», as the cashier prints it: "Лист назначения №7 — Принят в посту".
 */
enum PatientMedicationStatus: string implements HasBadge, HasIcon
{
    use HasLifecycle;
    use HasValues;

    case Ordered = 'ordered';
    case SentToFloor = 'sent_to_floor';
    case ReceivedAtStation = 'received_at_station';
    case Completed = 'completed';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Ordered => 'Заказан',
            self::SentToFloor => 'Отправлен на этаж',
            self::ReceivedAtStation => 'Принят в посту',
            self::Completed => 'Выполнен',
            self::Returned => 'Возвращён',
            self::Cancelled => 'Отменён',
        };
    }

    /**
     * The statuses this one may move to. The drug leaves the warehouse when the prescription is
     * sent to the floor; from then on it can only be given or returned, never "cancelled".
     *
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Ordered => [self::SentToFloor, self::Cancelled],
            self::SentToFloor => [self::ReceivedAtStation, self::Returned],
            self::ReceivedAtStation => [self::Completed, self::Returned],
            self::Completed, self::Returned, self::Cancelled => [],
        };
    }

    /** Yellow while the drug is on its way to the patient, green once it was given. */
    public function icon(): string
    {
        return match ($this) {
            self::Ordered => 'bx bx-test-tube text-warning',
            self::SentToFloor => 'bx bx-package text-warning',
            self::ReceivedAtStation => 'bx bx-check text-warning',
            self::Completed => 'bx bx-check-circle text-success',
            self::Returned => 'bx bx-undo text-secondary',
            self::Cancelled => 'bx bx-x-circle text-warning',
        };
    }

    /**
     * The colours of ServiceStatus for the same steps (the cashier lists both side by side): the
     * prescription is billed once the station received the drug.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Ordered, self::SentToFloor => 'bg-success-lt',
            self::ReceivedAtStation => 'bg-primary-lt',
            self::Completed => 'bg-info-lt',
            self::Returned => 'bg-secondary-lt',
            self::Cancelled => 'bg-warning-lt',
        };
    }
}
