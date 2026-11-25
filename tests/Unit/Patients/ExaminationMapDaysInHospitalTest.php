<?php

use App\Models\ExaminationMap;
use Illuminate\Support\Carbon;

it('counts the days of a finished stay from admission to discharge', function (): void {
    $map = new ExaminationMap(['admission_date' => '2025-11-01', 'discharge_date' => '2025-11-11']);

    expect($map->daysInHospital(Carbon::parse('2025-12-31')))->toBe(10);
});

it('counts the days of an open stay up to today', function (): void {
    $map = new ExaminationMap(['admission_date' => '2025-11-15']);

    expect($map->daysInHospital(Carbon::parse('2025-11-18 23:30')))->toBe(3)
        ->and($map->daysInHospital(Carbon::parse('2025-11-15 08:00')))->toBe(0);
});
