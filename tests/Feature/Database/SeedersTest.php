<?php

use App\Models\Admin;
use App\Models\DayOfWeek;
use App\Models\Document;
use App\Models\MainWarehouse;
use App\Models\Patient;
use App\Models\Reception;
use App\Models\ServiceType;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\VitalParameter;
use App\Models\WarehouseType;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DayOfWeekSeeder;
use Database\Seeders\DemoSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\seed;

it('seeds the reference data and can run again without duplicating it', function (): void {
    seed(DatabaseSeeder::class);
    seed(DatabaseSeeder::class);

    $admin = Admin::query()->where('email', config('medsystem.admin.email'))->sole();

    expect($admin->hasRole('main_admin', 'admin'))->toBeTrue()
        ->and(Role::findByName('main_admin', 'admin')->permissions)->toHaveCount(Permission::query()->count())
        ->and(DayOfWeek::query()->orderBy('id')->pluck('name')->all())->toBe(array_values(DayOfWeekSeeder::DAYS))
        ->and(MainWarehouse::query()->sole()->id)->toBe(1)
        ->and(WarehouseType::query()->orderBy('id')->pluck('name', 'id')->all())
        ->toBe([1 => 'Медикаменты', 2 => 'Хозяйственный материал', 3 => 'ИМН'])
        ->and(StaffPosition::query()->find(1)?->name)->toBe('Доктор')
        ->and(ServiceType::query()->find(1)?->name)->toBe('Данные отсутствуют')
        ->and(VitalParameter::query()->count())->toBe(9)
        ->and(Document::query()->count())->toBe(8)
        ->and(Document::query()->find(1)?->title)->toBe('Первичный сестринский осмотр. Детский.')
        ->and(Document::query()->find(1)->questions()->count())->toBe(32)
        ->and(Patient::query()->count())->toBe(0);
});

it('gives every staff position a role on the admin guard', function (): void {
    seed(DatabaseSeeder::class);

    StaffPosition::query()->pluck('name')->each(
        fn (string $position) => expect(Role::findByName($position, 'admin'))->not->toBeNull(),
    );
});

it('builds a demo hospital where doctors can be booked today', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);
    seed(DemoSeeder::class);

    $doctor = Staff::query()->whereRelation('professionalData', 'position_id', 1)->firstOrFail();
    $freeSlots = collect($doctor->availabilityOn(today()))->where('is_available', true);

    expect(Patient::query()->count())->toBe(40)
        ->and($freeSlots)->not->toBeEmpty()
        ->and(Reception::query()->whereDate('date', today())->exists())->toBeTrue()
        ->and(MainWarehouse::query()->find(1)->medication()->wherePivot('quantity', '>', 0)->exists())->toBeTrue();
});
