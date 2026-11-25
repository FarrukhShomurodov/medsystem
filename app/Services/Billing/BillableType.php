<?php

namespace App\Services\Billing;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasBadge;
use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The patient orders that go through the registrar and the cashier.
 *
 * Each kind keeps its own status column, so this is the one place that says which
 * of its statuses mean "ordered by the doctor", "confirmed by the registrar, to be paid"
 * and "paid at the cashier".
 */
enum BillableType: string implements HasBadge
{
    use HasValues;

    case Service = 'service';
    case Reception = 'reception';
    case Procedure = 'procedure';
    case Medication = 'medication';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Услуга',
            self::Reception => 'Приём',
            self::Procedure => 'Процедура',
            self::Medication => 'Лист назначения',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Service => 'bg-primary-lt',
            self::Reception => 'bg-success-lt',
            self::Procedure => 'bg-warning-lt',
            self::Medication => 'bg-info-lt',
        };
    }

    /**
     * @return class-string<PatientService|Reception|PatientProcedure|PatientMedication>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::Service => PatientService::class,
            self::Reception => Reception::class,
            self::Procedure => PatientProcedure::class,
            self::Medication => PatientMedication::class,
        };
    }

    /**
     * @return literal-string
     */
    public function table(): string
    {
        return match ($this) {
            self::Service => 'patient_services',
            self::Reception => 'receptions',
            self::Procedure => 'patient_procedures',
            self::Medication => 'patient_medications',
        };
    }

    /**
     * @return Builder<covariant Model>
     */
    public function query(): Builder
    {
        return $this->modelClass()::query();
    }

    /**
     * The day an order belongs to, which the cashier's date filter and the dashboard compare for
     * orders waiting for payment (a paid one counts on the day it was paid, see CashierItemsQuery):
     * the day of the service, appointment or procedure. A prescription only has the day it was written.
     *
     * @return literal-string
     */
    public function dateColumn(): string
    {
        return match ($this) {
            self::Service => 'patient_services.service_date',
            self::Reception => 'receptions.date',
            self::Procedure => 'patient_procedures.procedure_date',
            self::Medication => 'patient_medications.created_at',
        };
    }

    /** Ordered by the doctor, waiting for the registrar. */
    public function orderedStatus(): ServiceStatus|ReceptionStatus|PatientMedicationStatus
    {
        return match ($this) {
            self::Service, self::Procedure => ServiceStatus::Assigned,
            self::Reception => ReceptionStatus::New,
            self::Medication => PatientMedicationStatus::Ordered,
        };
    }

    /** Confirmed by the registrar, waiting for the cashier. */
    public function payableStatus(): ServiceStatus|ReceptionStatus|PatientMedicationStatus
    {
        return match ($this) {
            self::Service, self::Procedure => ServiceStatus::Confirmed,
            self::Reception => ReceptionStatus::Confirmed,
            self::Medication => PatientMedicationStatus::SentToFloor,
        };
    }

    /**
     * Set by the cashier's payment. A prescription has no "paid" status of its own:
     * paying for it is what releases it to the nurse station.
     */
    public function paidStatus(): ServiceStatus|ReceptionStatus|PatientMedicationStatus
    {
        return match ($this) {
            self::Service, self::Procedure => ServiceStatus::Paid,
            self::Reception => ReceptionStatus::Paid,
            self::Medication => PatientMedicationStatus::ReceivedAtStation,
        };
    }

    /**
     * The paid status and the ones that follow it while the order still stands for the payment:
     * the service carried out, the visit done, the drug given. A returned prescription is left out,
     * since it is returned unpaid as well; a paid one keeps its paid_at.
     *
     * @return list<ServiceStatus|ReceptionStatus|PatientMedicationStatus>
     */
    public function paidStatuses(): array
    {
        return match ($this) {
            self::Service, self::Procedure => array_values(array_filter(ServiceStatus::cases(), fn (ServiceStatus $status): bool => $status->isPaid())),
            self::Reception => array_values(array_filter(ReceptionStatus::cases(), fn (ReceptionStatus $status): bool => $status->isPaid())),
            self::Medication => [PatientMedicationStatus::ReceivedAtStation, PatientMedicationStatus::Completed],
        };
    }
}
