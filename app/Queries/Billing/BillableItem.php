<?php

namespace App\Queries\Billing;

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Services\Billing\Amount;
use App\Services\Billing\BillableType;

/**
 * One order of a patient as the registrar and the cashier see it.
 */
final readonly class BillableItem
{
    /** The currency of prices that do not name one. */
    public const string DEFAULT_CURRENCY = 'UZS';

    public function __construct(
        public BillableType $type,
        public int $id,
        public ?int $patientId,
        public string $name,
        public string $patientName,
        public ?string $staffName,
        public Amount $price,
        public string $currency,
        public string $occurredAt,
        public ServiceStatus|ReceptionStatus|PatientMedicationStatus $status,
    ) {}

    /** "service:12": the value the cashier form posts for this order. */
    public function key(): string
    {
        return "{$this->type->value}:{$this->id}";
    }

    public function isPayable(): bool
    {
        return $this->status === $this->type->payableStatus();
    }
}
