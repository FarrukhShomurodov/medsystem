<?php

use App\Services\Scheduling\SlotGrid;
use App\Services\Scheduling\TimeRange;

it('cuts the day into back-to-back slots of the appointment length', function (): void {
    $slots = (new SlotGrid(20))->slots();

    expect($slots)->toHaveCount(72)
        ->and($slots[0])->toEqual(new TimeRange(0, 20))
        ->and(end($slots)->startTime())->toBe('23:40');
});

it('has no slots for an appointment length that would never reach the end of the day', function (int $minutes): void {
    // Regression: the old loop added 0 (or a negative number of) minutes forever.
    expect((new SlotGrid($minutes))->slots())->toBe([]);
})->with([0, -10]);

it('holds only the spans one length long that start a whole number of lengths after midnight', function (): void {
    $grid = new SlotGrid(20);

    expect($grid->has(TimeRange::fromTimes('00:00', '00:20')))->toBeTrue()
        ->and($grid->has(TimeRange::fromTimes('10:40', '11:00')))->toBeTrue()
        ->and($grid->has(TimeRange::fromTimes('23:40', '24:00')))->toBeTrue()
        ->and($grid->has(TimeRange::fromTimes('10:05', '10:25')))->toBeFalse()
        ->and($grid->has(TimeRange::fromTimes('10:30', '10:50')))->toBeFalse()
        ->and($grid->has(TimeRange::fromTimes('10:00', '10:40')))->toBeFalse()
        ->and($grid->has(new TimeRange(24 * 60, 24 * 60 + 20)))->toBeFalse()
        ->and((new SlotGrid(0))->has(new TimeRange(0, 0)))->toBeFalse();
});

it('offers only the slots lying completely inside the reception hours', function (): void {
    // Regression: the slot starting at the end of the hours, and one running past it, were offered.
    $grid = new SlotGrid(30);
    $hours = [TimeRange::fromTimes('09:00', '10:00'), TimeRange::fromTimes('14:00', '14:45')];

    $available = array_column(array_filter($grid->availability($hours), fn (array $slot): bool => $slot['is_available']), 'time');

    expect(array_values($available))->toBe(['09:00', '09:30', '14:00'])
        ->and($grid->availability($hours)[0])->toBe(['time' => '00:00', 'is_available' => false, 'admission_period' => 30]);
});

it('books every slot an appointment overlaps, wherever it starts', function (): void {
    $grid = new SlotGrid(20);
    $bookings = $grid->bookings([41 => TimeRange::fromTimes('09:15', '09:35'), 42 => TimeRange::fromTimes('10:00', '10:20')]);

    $booked = array_values(array_filter($bookings, fn (array $slot): bool => $slot['is_booked']));

    expect(array_map(fn (array $slot): array => [$slot['time'], $slot['reception_id']], $booked))
        ->toBe([['09:00', 41], ['09:20', 41], ['10:00', 42]]);
});
