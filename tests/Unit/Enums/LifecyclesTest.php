<?php

use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Enums\SupplyRequestStatus;
use App\Enums\TransferStatus;

/*
| The five status lifecycles keep their rules in one list each, nextStatuses(); everything else
| (canTransitionTo, isFinal, the final statuses) follows from it. These checks hold for all of them.
*/

dataset('lifecycles', [
    'service or procedure order' => [ServiceStatus::class],
    'appointment' => [ReceptionStatus::class],
    'prescription' => [PatientMedicationStatus::class],
    'transfer' => [TransferStatus::class],
    'supply request' => [SupplyRequestStatus::class],
]);

/**
 * The statuses reachable from the given one by one or more transitions.
 *
 * @template TStatus of BackedEnum
 *
 * @param  TStatus  $from
 * @return list<TStatus>
 */
function lifecycleReachableFrom(BackedEnum $from): array
{
    $reached = [];
    $queue = $from->nextStatuses();

    while ($queue !== []) {
        $status = array_shift($queue);

        if (! in_array($status, $reached, true)) {
            $reached[] = $status;
            array_push($queue, ...$status->nextStatuses());
        }
    }

    return $reached;
}

it('allows exactly the transitions its list names', function (string $lifecycle): void {
    foreach ($lifecycle::cases() as $from) {
        foreach ($lifecycle::cases() as $to) {
            expect($from->canTransitionTo($to))->toBe(in_array($to, $from->nextStatuses(), true), "{$from->value} -> {$to->value}");
        }
    }
})->with('lifecycles');

it('names each next status once and never the status itself', function (string $lifecycle): void {
    foreach ($lifecycle::cases() as $status) {
        $next = array_column($status->nextStatuses(), 'value');

        expect($next)->not->toContain($status->value)
            ->and(array_unique($next))->toHaveCount(count($next));
    }
})->with('lifecycles');

it('starts every record in one status from which all the others can be reached', function (string $lifecycle): void {
    $successors = array_merge(...array_map(fn (BackedEnum $status): array => $status->nextStatuses(), $lifecycle::cases()));
    $starts = array_values(array_filter($lifecycle::cases(), fn (BackedEnum $status): bool => ! in_array($status, $successors, true)));

    expect($starts)->toHaveCount(1)
        ->and(lifecycleReachableFrom($starts[0]))->toHaveCount(count($lifecycle::cases()) - 1);
})->with('lifecycles');

it('never leads back to a status it left, so every path ends in a final status', function (string $lifecycle): void {
    foreach ($lifecycle::cases() as $status) {
        expect(lifecycleReachableFrom($status))->not->toContain($status);
    }
})->with('lifecycles');

it('treats exactly the statuses without a next one as final', function (string $lifecycle): void {
    $final = array_values(array_filter($lifecycle::cases(), fn (BackedEnum $status): bool => $status->nextStatuses() === []));

    expect($final)->not->toBeEmpty()
        ->and($lifecycle::finalStatuses())->toBe($final)
        ->and($lifecycle::finalValues())->toBe(array_column($final, 'value'));

    foreach ($lifecycle::cases() as $status) {
        expect($status->isFinal())->toBe(in_array($status, $final, true));
    }
})->with('lifecycles');

it('names the final statuses of each lifecycle', function (): void {
    expect(ServiceStatus::finalStatuses())->toBe([ServiceStatus::Completed, ServiceStatus::Cancelled])
        ->and(ReceptionStatus::finalStatuses())->toBe([ReceptionStatus::Done, ReceptionStatus::Cancelled])
        ->and(PatientMedicationStatus::finalStatuses())->toBe([PatientMedicationStatus::Completed, PatientMedicationStatus::Returned, PatientMedicationStatus::Cancelled])
        ->and(TransferStatus::finalStatuses())->toBe([TransferStatus::Completed, TransferStatus::Canceled])
        ->and(SupplyRequestStatus::finalStatuses())->toBe([SupplyRequestStatus::Approved, SupplyRequestStatus::Rejected]);
});
