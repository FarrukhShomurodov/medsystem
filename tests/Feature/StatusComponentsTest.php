<?php

use App\Enums\PatientMedicationStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\View\Components\StatusBadge;
use App\View\Components\StatusIcon;
use Illuminate\Support\Facades\Blade;

/*
| Statuses are drawn by two Blade components from the enum, not by HTML built in the models.
*/

it('draws a status as a badge in its colour, keeping the classes the page adds', function (): void {
    expect(trim(Blade::render('<x-status-badge :status="$status" class="me-1" />', ['status' => ServiceStatus::Paid])))
        ->toBe('<span class="badge bg-primary-lt me-1">Оплачена</span>');
});

it('draws a prescription status as an icon whose tooltip is the label', function (): void {
    expect(trim(Blade::render('<x-status-icon :status="$status" />', ['status' => PatientMedicationStatus::Completed])))
        ->toBe('<i class="bx bx-check-circle text-success" role="img" title="Выполнен" aria-label="Выполнен"></i>');
});

it('draws the statuses of every billable order as badges, as the cashier lists them', function (): void {
    expect(trim(Blade::render('<x-status-badge :status="$status" />', ['status' => ReceptionStatus::Paid])))
        ->toBe('<span class="badge bg-primary-lt">Оплачен</span>')
        ->and(trim(Blade::render('<x-status-badge :status="$status" />', ['status' => PatientMedicationStatus::SentToFloor])))
        ->toBe('<span class="badge bg-success-lt">Отправлен на этаж</span>');
});

it('hides the icon from screen readers where the label is printed next to it', function (): void {
    expect(trim(Blade::render('<x-status-icon :status="$status" decorative />', ['status' => PatientMedicationStatus::Completed])))
        ->toBe('<i class="bx bx-check-circle text-success" aria-hidden="true"></i>');
});

it('takes only values that can be drawn that way', function (): void {
    expect(fn (): StatusBadge => new StatusBadge(PaymentStatus::Paid))->toThrow(TypeError::class)
        ->and(fn (): StatusIcon => new StatusIcon(ServiceStatus::Paid))->toThrow(TypeError::class);
});
