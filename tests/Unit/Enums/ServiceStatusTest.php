<?php

use App\Enums\ServiceStatus;

/*
| The lifecycle of a service or procedure ordered for a patient.
*/

it('moves an order forward through registrar confirmation, payment and the procedure', function (ServiceStatus $from, ServiceStatus $to): void {
    expect($from->canTransitionTo($to))->toBeTrue();
})->with([
    'registrar confirms' => [ServiceStatus::Assigned, ServiceStatus::Confirmed],
    'cashier takes payment' => [ServiceStatus::Confirmed, ServiceStatus::Paid],
    'procedure starts' => [ServiceStatus::Paid, ServiceStatus::InProcess],
    'done at once' => [ServiceStatus::Paid, ServiceStatus::Completed],
    'procedure ends' => [ServiceStatus::InProcess, ServiceStatus::Completed],
    'cancelled before confirmation' => [ServiceStatus::Assigned, ServiceStatus::Cancelled],
    'cancelled before payment' => [ServiceStatus::Confirmed, ServiceStatus::Cancelled],
]);

it('never pays an order that the registrar has not confirmed', function (): void {
    expect(ServiceStatus::Assigned->canTransitionTo(ServiceStatus::Paid))->toBeFalse();
});

it('never carries out an order before it is paid', function (ServiceStatus $from): void {
    expect($from->canTransitionTo(ServiceStatus::InProcess))->toBeFalse()
        ->and($from->canTransitionTo(ServiceStatus::Completed))->toBeFalse();
})->with([ServiceStatus::Assigned, ServiceStatus::Confirmed]);

it('never puts an order back into the registrar basket once it moved on', function (ServiceStatus $from): void {
    expect($from->canTransitionTo(ServiceStatus::Assigned))->toBeFalse();
})->with(ServiceStatus::cases());

it('cancels a paid order only as a refund, not as a status change', function (ServiceStatus $from): void {
    expect($from->canTransitionTo(ServiceStatus::Cancelled))->toBeFalse();
})->with([ServiceStatus::Paid, ServiceStatus::InProcess]);

it('lets nothing leave a completed or cancelled order', function (ServiceStatus $final): void {
    expect($final->isFinal())->toBeTrue()
        ->and($final->nextStatuses())->toBe([]);
})->with([ServiceStatus::Completed, ServiceStatus::Cancelled]);

it('leaves the payment out of the statuses a clinician may set', function (ServiceStatus $status, array $manual): void {
    expect($status->manualNextStatuses())->toBe($manual);
})->with([
    'assigned' => [ServiceStatus::Assigned, [ServiceStatus::Confirmed, ServiceStatus::Cancelled]],
    'confirmed' => [ServiceStatus::Confirmed, [ServiceStatus::Cancelled]],
    'paid' => [ServiceStatus::Paid, [ServiceStatus::InProcess, ServiceStatus::Completed]],
    'completed' => [ServiceStatus::Completed, []],
]);

it('treats an order as paid from the payment on, and a cancelled one as never paid', function (): void {
    expect(array_values(array_filter(ServiceStatus::cases(), fn (ServiceStatus $status): bool => $status->isPaid())))
        ->toBe([ServiceStatus::Paid, ServiceStatus::InProcess, ServiceStatus::Completed]);
});

it('becomes paid only through the payment at the cashier', function (): void {
    $intoPaid = [];

    foreach (ServiceStatus::cases() as $from) {
        foreach ($from->nextStatuses() as $next) {
            if (! $from->isPaid() && $next->isPaid()) {
                $intoPaid[] = [$from, $next];
            }
        }
    }

    expect($intoPaid)->toBe([[ServiceStatus::Confirmed, ServiceStatus::Paid]]);
});
