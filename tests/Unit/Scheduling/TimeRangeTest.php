<?php

use App\Services\Scheduling\TimeRange;

it('reads times as forms send them and as PostgreSQL returns them', function (): void {
    expect(TimeRange::fromTimes('09:05', '10:30:00'))
        ->start->toBe(545)
        ->end->toBe(630)
        ->and(TimeRange::fromTimes('9:05', '10:30')->startTime())->toBe('09:05');
});

it('treats touching appointments as not overlapping', function (): void {
    $tenToTwenty = TimeRange::fromTimes('10:00', '10:20');

    expect($tenToTwenty->overlaps(TimeRange::fromTimes('10:20', '10:40')))->toBeFalse()
        ->and($tenToTwenty->overlaps(TimeRange::fromTimes('09:40', '10:00')))->toBeFalse()
        ->and($tenToTwenty->overlaps(TimeRange::fromTimes('10:19', '10:40')))->toBeTrue()
        ->and($tenToTwenty->overlaps(TimeRange::fromTimes('09:00', '11:00')))->toBeTrue();
});

it('contains a span only when the span ends by its end', function (): void {
    $hours = TimeRange::fromTimes('09:00', '10:00');

    expect($hours->contains(TimeRange::fromTimes('09:40', '10:00')))->toBeTrue()
        ->and($hours->contains(TimeRange::fromTimes('09:50', '10:10')))->toBeFalse()
        ->and($hours->contains(TimeRange::fromTimes('10:00', '10:20')))->toBeFalse();
});

it('rejects spans ending before they start and malformed times', function (string $start, string $end): void {
    expect(fn (): TimeRange => TimeRange::fromTimes($start, $end))->toThrow(InvalidArgumentException::class)
        ->and(TimeRange::tryFromTimes($start, $end))->toBeNull();
})->with([
    ['10:00', '09:00'],
    ['ten', '11:00'],
]);
