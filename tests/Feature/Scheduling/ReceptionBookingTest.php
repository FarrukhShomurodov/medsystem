<?php

use App\Models\ExaminationMap;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffAbsence;
use App\Models\StaffDocument;
use App\Services\Scheduling\ReceptionService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Pest\Expectation;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;

/*
| Booking an appointment from the reception calendars (POST /api/receptions).
*/

beforeEach(function (): void {
    $this->registrar = adminWithPermissions('create_receptions');
    $this->doctor = SchedulingFixtures::doctor();
});

it('books an appointment as a new one in the patient\'s active examination map', function (): void {
    $booking = SchedulingFixtures::booking($this->doctor, '10:00', '10:20');
    $map = ExaminationMap::factory()->create(['patient_id' => $booking['patient_id'], 'is_active' => true]);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', [...$booking, 'status' => null])
        ->assertCreated()
        ->assertJsonPath('status', 'new');

    expect(Reception::query()->sole())
        ->staff_id->toBe($this->doctor->id)
        ->examination_map_id->toBe($map->id)
        ->start_time->toBe('10:00:00')
        ->end_time->toBe('10:20:00');
});

it('refuses an appointment that overlaps one already booked with the doctor', function (array $booked, array $requested): void {
    // Regression: the server accepted any time, so two registrars could book the same slot.
    SchedulingFixtures::appointment($this->doctor, ...$booked);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, ...$requested))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Это время уже занято: выберите другой слот.');

    expect(Reception::query()->count())->toBe(1);
})->with([
    // The requests keep to the doctor's 20-minute grid; the appointments off it were booked on an
    // older one, before the appointment length changed.
    'the same slot' => [['10:00', '10:20'], ['10:00', '10:20']],
    'starting inside it' => [['10:10', '10:30'], ['10:20', '10:40']],
    'ending inside it' => [['10:10', '10:30'], ['10:00', '10:20']],
    'around one made with a shorter length' => [['10:05', '10:15'], ['10:00', '10:20']],
]);

it('books exactly the doctor\'s appointment length, so one booking cannot block the day', function (string $start, string $end): void {
    // The end time came from the browser as it was: a crafted 08:00-18:00 booking took the whole day.
    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, $start, $end))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Приём у этого сотрудника длится 20 мин.: обновите календарь и выберите время заново.');

    expect(Reception::query()->exists())->toBeFalse();
})->with([
    'the whole day' => ['08:00', '18:00'],
    'a single minute' => ['10:00', '10:01'],
]);

it('books only within the reception hours of the doctor', function (array $hours, string $start, string $end): void {
    // The server took any time of the day; only the calendars kept to the reception hours.
    $doctor = SchedulingFixtures::doctor(20, [$hours]);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($doctor, $start, $end))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Это время вне часов приёма сотрудника: выберите слот в календаре.');

    expect(Reception::query()->exists())->toBeFalse();
})->with([
    'before the hours' => [['09:00', '13:00'], '08:40', '09:00'],
    'starting when they end' => [['09:00', '13:00'], '13:00', '13:20'],
    'running past their end' => [['09:00', '12:50'], '12:40', '13:00'],
]);

it('books only a start on the doctor\'s slot grid, which runs from midnight', function (int $minutes, string $start, string $end, array $hours = ['09:00', '13:00']): void {
    // The calendars offer only the slots of the grid, but the server took any start of the right
    // length: a crafted 10:05-10:25 left gaps on both sides that no calendar could offer.
    $doctor = SchedulingFixtures::doctor($minutes, [$hours]);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($doctor, $start, $end))
        ->assertUnprocessable()
        ->assertJsonPath('message', "Это время не попадает в сетку приёма сотрудника (слоты по {$minutes} мин.): обновите календарь и выберите время заново.");

    expect(Reception::query()->exists())->toBeFalse();
})->with([
    'between two 20-minute slots' => [20, '10:05', '10:25'],
    'on a 30-minute grid, not the 20-minute one' => [20, '10:30', '10:50'],
    'on a 20-minute grid, not the 15-minute one' => [15, '10:20', '10:35'],
    'on a 15-minute grid, not the 30-minute one' => [30, '10:15', '10:45'],
    'at the start of hours that begin between two slots' => [20, '09:10', '09:30', ['09:10', '12:50']],
    'on the hour, which a 7-minute grid from midnight misses' => [7, '09:00', '09:07'],
]);

it('books the first and the last slot the calendars offer inside the reception hours', function (int $minutes, array $hours, array $first, array $last): void {
    $doctor = SchedulingFixtures::doctor($minutes, [$hours]);
    $offered = collect($doctor->availabilityOn(today()))->where('is_available', true)->pluck('time');

    expect([$offered->first(), $offered->last()])->toBe([$first[0], $last[0]]);

    foreach ([$first, $last] as [$start, $end]) {
        actingAs($this->registrar, 'admin')
            ->postJson('/api/receptions', SchedulingFixtures::booking($doctor, $start, $end))
            ->assertCreated();
    }
})->with([
    'hours on the grid' => [20, ['09:00', '13:00'], ['09:00', '09:20'], ['12:40', '13:00']],
    'hours between the slots' => [20, ['09:10', '12:50'], ['09:20', '09:40'], ['12:20', '12:40']],
    'a length that does not divide the hour' => [7, ['09:00', '13:00'], ['09:06', '09:13'], ['12:50', '12:57']],
]);

it('keeps an appointment booked on an older grid, and lets it block the slots it overlaps', function (): void {
    // 10:15-10:30 was booked while the doctor saw a patient every 15 minutes; the length is 20 now.
    SchedulingFixtures::appointment($this->doctor, '10:15', '10:30');

    foreach ([['10:00', '10:20'], ['10:20', '10:40']] as [$start, $end]) {
        actingAs($this->registrar, 'admin')
            ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, $start, $end))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Это время уже занято: выберите другой слот.');
    }

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:40', '11:00'))
        ->assertCreated();

    // Still covered: the staff form does not ask the registrars to move it.
    expect(app(ReceptionService::class)->uncoveredAppointmentsOf($this->doctor))->toBeEmpty();
});

it('refuses a booking for a day the doctor has no reception hours', function (): void {
    $booking = SchedulingFixtures::booking($this->doctor, '10:00', '10:20', ['date' => today()->addDay()->toDateString()]);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', $booking)
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Это время вне часов приёма сотрудника: выберите слот в календаре.');
});

it('books only the appointment services of the doctor and only staff who take appointments', function (): void {
    // A crafted request could book any service with anyone, staff without an appointment length included.
    $otherService = Service::factory()->appointment()->create();

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20', ['service_id' => $otherService->id]))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Сотрудник не ведёт приём по этой услуге.');

    $nurse = Staff::factory()->create();

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20', ['staff_id' => $nurse->id]))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Сотрудник не ведёт приём по этой услуге.');

    expect(Reception::query()->exists())->toBeFalse();
});

it('refuses to book a service taken off the price list', function (): void {
    // "Активна" off withdraws the service: the order forms hid it, but it was still booked and billed.
    $this->doctor->services()->update(['is_active' => false]);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20'))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Сотрудник не ведёт приём по этой услуге.');

    expect(Reception::query()->exists())->toBeFalse();
});

it('books back-to-back appointments and the slot of a cancelled one', function (): void {
    SchedulingFixtures::appointment($this->doctor, '10:00', '10:20');
    SchedulingFixtures::appointment($this->doctor, '10:20', '10:40', ['status' => 'cancelled']);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:20', '10:40'))
        ->assertCreated();

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '09:40', '10:00'))
        ->assertCreated();
});

it('lets a doctor\'s account book only the doctor\'s own calendar', function (Closure $doctorAccount): void {
    // The booking page offers a doctor only their own services, but the endpoint booked anyone who
    // offers the same service.
    $doctorAdmin = $doctorAccount('create_receptions');
    $colleague = SchedulingFixtures::doctor();
    $colleague->services()->sync([$doctorAdmin->staff->services()->value('services.id')]);

    actingAs($doctorAdmin, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($colleague, '10:00', '10:20'))
        ->assertForbidden()
        ->assertJsonPath('message', 'Врач работает только со своим календарём приёмов.');

    expect(Reception::query()->exists())->toBeFalse();

    actingAs($doctorAdmin, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($doctorAdmin->staff, '10:00', '10:20'))
        ->assertCreated();
})->with(SchedulingFixtures::doctorAccounts());

it('refuses a doctor\'s account an employee id that names nobody, as it refuses a colleague\'s', function (): void {
    // An unknown id went on to validation (422) while a colleague's was refused (403), which told the
    // doctor which ids exist.
    $doctorAdmin = SchedulingFixtures::doctorAccount('create_receptions');
    $unknown = Staff::query()->max('id') + 1;

    actingAs($doctorAdmin, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($doctorAdmin->staff, '10:00', '10:20', ['staff_id' => $unknown]))
        ->assertForbidden()
        ->assertJsonPath('message', 'Врач работает только со своим календарём приёмов.');

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20', ['staff_id' => $unknown]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('staff_id');
});

it('lets an account without a doctor profile book any employee', function (?int $position): void {
    $account = SchedulingFixtures::account($position, 'create_receptions');

    actingAs($account, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20'))
        ->assertCreated();
})->with(SchedulingFixtures::WITHOUT_DOCTOR_PROFILE);

it('books the same time with another doctor', function (): void {
    SchedulingFixtures::appointment($this->doctor, '10:00', '10:20');
    $colleague = SchedulingFixtures::doctor();

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($colleague, '10:00', '10:20'))
        ->assertCreated();
});

it('refuses to book a doctor who is absent or dismissed', function (): void {
    StaffAbsence::factory()->for($this->doctor)->create(['type' => 'day', 'start_date' => today()->toDateString()]);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20'))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Сотрудник отсутствует в этот день.');

    $dismissed = SchedulingFixtures::doctor();
    StaffDocument::query()->whereBelongsTo($dismissed)->update(['is_work' => false]);

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($dismissed, '10:00', '10:20'))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Сотрудник уволен, запись к нему невозможна.');

    expect(Reception::query()->exists())->toBeFalse();
});

it('validates the time, the date and the initial status of a booking', function (array $overrides, string $field): void {
    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20', $overrides))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'end before start' => [['end_time' => '09:40'], 'end_time'],
    'not a time' => [['start_time' => 'ten'], 'start_time'],
    'a past day' => [['date' => '2000-01-01'], 'date'],
    'a status reached later' => [['status' => 'done'], 'status'],
]);

it('lets only the cash desk book an appointment as already paid', function (): void {
    // create_receptions alone could create a paid appointment, skipping the cash desk (accept_payments).
    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20', ['status' => 'paid']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status' => 'Отметить приём оплаченным может только касса.']);

    actingAs(adminWithPermissions('create_receptions', 'accept_payments'), 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20', ['status' => 'paid']))
        ->assertCreated()
        ->assertJsonPath('status', 'paid');
});

it('lets the database refuse an overlapping appointment written past the service', function (): void {
    SchedulingFixtures::appointment($this->doctor, '10:00', '10:20');

    // A failed statement aborts the PostgreSQL transaction; the savepoint keeps the test's one usable.
    DB::beginTransaction();

    expect(fn (): Reception => SchedulingFixtures::appointment($this->doctor, '10:10', '10:30'))
        ->toThrow(fn (QueryException $e): Expectation => expect($e->getCode())->toBe('23P01'));

    DB::rollBack();

    expect(Reception::query()->count())->toBe(1);
});

it('adds the overlap constraint over double bookings made before it, leaving only those out', function (): void {
    // An exclusion constraint cannot be added NOT VALID, so the double bookings that earlier
    // versions let into a database made this migration fail.
    $migration = require database_path('migrations/2025_11_19_130100_add_overlap_constraint_to_receptions_table.php');
    $migration->down();

    SchedulingFixtures::appointment($this->doctor, '10:00', '10:20');
    SchedulingFixtures::appointment($this->doctor, '10:10', '10:30');
    SchedulingFixtures::appointment($this->doctor, '11:00', '11:20');

    $migration->up();

    DB::beginTransaction();

    expect(fn (): Reception => SchedulingFixtures::appointment($this->doctor, '11:10', '11:30'))
        ->toThrow(fn (QueryException $e): Expectation => expect($e->getCode())->toBe('23P01'));

    DB::rollBack();

    actingAs($this->registrar, 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:20', '10:40'))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Это время уже занято: выберите другой слот.');

    expect(Reception::query()->count())->toBe(3);
});

it('forbids booking without create_receptions', function (): void {
    actingAs(adminWithPermissions('view_appointment_receptions'), 'admin')
        ->postJson('/api/receptions', SchedulingFixtures::booking($this->doctor, '10:00', '10:20'))
        ->assertForbidden();

    expect(Reception::query()->exists())->toBeFalse();
});
