<?php

use App\Enums\Contracts\HasBadge;
use App\Enums\Contracts\HasIcon;
use App\Enums\Contracts\HasLabel;
use App\Enums\EquipmentStatus;
use App\Enums\Gender;
use App\Enums\PackagingType;
use App\Enums\PatientMedicationStatus;
use App\Enums\ReceptionStatus;
use App\Enums\ServiceStatus;
use App\Enums\SupplyRequestStatus;
use App\Enums\TransferStatus;
use App\Services\Billing\BillableType;

/*
| Every enum in app/Enums is a closed vocabulary stored in a column: string-backed, with a Russian
| label for each value, offered to the forms by options().
*/

dataset('vocabularies', function (): array {
    $enums = [];

    foreach (glob(__DIR__.'/../../../app/Enums/*.php') as $file) {
        $enum = 'App\\Enums\\'.basename($file, '.php');
        $enums[class_basename($enum)] = [$enum];
    }

    return $enums;
});

it('is a string-backed enum with a label', function (string $enum): void {
    expect(enum_exists($enum))->toBeTrue()
        ->and((new ReflectionEnum($enum))->getBackingType()?->getName())->toBe('string')
        ->and(is_subclass_of($enum, HasLabel::class))->toBeTrue();
})->with('vocabularies');

it('stores ASCII snake_case values and shows a different Russian label for each', function (string $enum): void {
    $labels = array_map(fn (HasLabel $case): string => $case->label(), $enum::cases());

    expect($enum::values())->each->toMatch('/^[a-z]+(_[a-z]+)*$/')
        ->and(array_unique($labels))->toHaveCount(count($labels))
        ->and($labels)->each->toMatch('/\p{Cyrillic}/u');
})->with('vocabularies');

it('offers its values to a select in declaration order, with their labels', function (string $enum): void {
    expect($enum::options())->toBe(array_combine(
        array_column($enum::cases(), 'value'),
        array_map(fn (HasLabel $case): string => $case->label(), $enum::cases()),
    ));
})->with('vocabularies');

it('gives the values that were stored as Russian words their labels back', function (): void {
    expect(EquipmentStatus::options())->toBe(['working' => 'Работает', 'broken' => 'Сломан'])
        ->and(PackagingType::options())->toBe(['blister' => 'Блистер', 'vial' => 'Флакон', 'tube' => 'Тюбик'])
        ->and(Gender::options())->toBe(['male' => 'Мужской', 'female' => 'Женский']);
});

it('colours every status and kind of order shown as a badge with a light Tabler colour', function (HasBadge $value): void {
    expect($value->badgeClass())->toMatch('/^bg-[a-z]+-lt$/');
})->with([
    ...ServiceStatus::cases(),
    ...ReceptionStatus::cases(),
    ...PatientMedicationStatus::cases(),
    ...TransferStatus::cases(),
    ...SupplyRequestStatus::cases(),
    ...BillableType::cases(),
]);

it('draws every prescription status as a coloured boxicons icon', function (HasIcon $status): void {
    expect($status->icon())->toMatch('/^bx bx-[a-z-]+ text-[a-z]+$/');
})->with(PatientMedicationStatus::cases());
