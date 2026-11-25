<?php

use App\Enums\ScheduleType;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\StaffAbsence;
use App\Models\StaffDocument;
use App\Models\StaffPosition;
use App\Models\StaffProfessionalData;
use App\Models\StaffSchedule;
use App\Services\Scheduling\AppointmentCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;

/*
| The appointment grid the booking calendars draw: free slots, booked slots, the doctor's week.
*/

/**
 * @param  list<array<string, mixed>>  $slots
 * @return list<string>
 */
function slotTimes(array $slots, string $flag): array
{
    return collect($slots)->where($flag, true)->pluck('time')->values()->all();
}

it('offers the slots inside the reception hours, not the one starting when they end', function (): void {
    // Regression: betweenIncluded() offered 10:00, an appointment that would end after the hours.
    $doctor = SchedulingFixtures::doctor(20, [['09:00', '10:00'], ['11:00', '11:50']]);

    expect(slotTimes($doctor->availabilityOn(today()), 'is_available'))
        ->toBe(['09:00', '09:20', '09:40', '11:00', '11:20']);
});

it('marks every slot an appointment overlaps as booked, also off the doctor\'s grid', function (): void {
    // Regression: a slot counted as booked only when an appointment started exactly at its time.
    $doctor = SchedulingFixtures::doctor(20);
    $reception = SchedulingFixtures::appointment($doctor, '09:15', '09:35');
    SchedulingFixtures::appointment($doctor, '10:00', '10:20', ['status' => 'cancelled']);

    $booked = collect($doctor->bookingsOn(today()))->where('is_booked', true);

    expect($booked->pluck('time')->all())->toBe(['09:00', '09:20'])
        ->and($booked->pluck('reception_id')->unique()->all())->toBe([$reception->id]);
});

it('returns an empty grid instead of looping for a missing or non-positive appointment length', function (?int $minutes): void {
    // Regression: an admission_period of 0 never reached the end of the day.
    $doctor = SchedulingFixtures::doctor();
    StaffProfessionalData::query()->whereBelongsTo($doctor)->update(['admission_period' => $minutes]);
    SchedulingFixtures::appointment($doctor, '09:00', '09:20');
    $doctor->unsetRelation('professionalData');

    expect($doctor->availabilityOn(today()))->toBe([])
        ->and($doctor->bookingsOn(today()))->toBe([]);
})->with([0, -15, null]);

it('returns an empty grid for staff without professional data', function (): void {
    expect(Staff::factory()->create()->availabilityOn(today()))->toBe([]);
});

it('works out "today" in the app timezone for the grid and for the doctor pickers alike', function (): void {
    // Regression: Staff parsed dates in Asia/Tashkent while the pickers used the app timezone, so
    // shortly after midnight they disagreed about the weekday.
    $default = date_default_timezone_get();
    date_default_timezone_set('UTC');
    config(['app.timezone' => 'UTC']);

    try {
        // 22:30 UTC on a Tuesday is already Wednesday in Tashkent.
        Carbon::setTestNow(Carbon::parse('2026-11-17 22:30:00', 'UTC'));
        $doctor = SchedulingFixtures::doctor(20, [['09:00', '10:00']], today());

        expect(slotTimes($doctor->availabilityOn(today()), 'is_available'))->toBe(['09:00', '09:20', '09:40'])
            ->and(Staff::query()->workingOn(today())->pluck('id'))->toContain($doctor->id);
    } finally {
        Carbon::setTestNow();
        date_default_timezone_set($default);
        config(['app.timezone' => $default]);
    }
});

it('lists the staff of a position working on a given date for the doctor pickers', function (): void {
    $wednesday = CarbonImmutable::parse('next wednesday');
    $doctor = SchedulingFixtures::doctor(on: $wednesday);
    $absent = SchedulingFixtures::doctor(on: $wednesday);
    StaffAbsence::factory()->for($absent)->create(['type' => 'day', 'start_date' => $wednesday->toDateString()]);
    $nurse = SchedulingFixtures::doctor(on: $wednesday);
    StaffProfessionalData::query()->whereBelongsTo($nurse)->update(['position_id' => StaffPosition::factory()->create(['name' => 'Медсестра'])->id]);

    $doctorsOn = fn (CarbonImmutable $date): array => Staff::query()
        ->workingOn($date)
        ->inPosition($doctor->professionalData->position_id)
        ->pluck('id')
        ->all();

    expect($doctorsOn($wednesday))->toBe([$doctor->id])
        ->and($doctorsOn($wednesday->addDay()))->toBe([]);
});

it('returns the doctor\'s week with free and booked slots, and only the appointments kept on the days of absence', function (): void {
    $monday = CarbonImmutable::parse('next monday');
    $doctor = SchedulingFixtures::doctor(30, [['09:00', '10:00']], $monday);
    $reception = SchedulingFixtures::appointment($doctor, '09:30', '10:00', ['date' => $monday->toDateString()]);
    $admin = adminWithPermissions('view_reception_doctor_calendar');

    $week = actingAs($admin, 'admin')
        ->getJson("/api/staff-availability-week/{$doctor->id}?start={$monday->toDateString()}")
        ->assertOk()
        ->json();

    expect(collect($week)->map(fn (array $slot): array => [$slot['date'], $slot['time'], $slot['is_available'], $slot['is_booked']])->where(2, true)->values()->all())
        ->toBe([
            [$monday->toDateString(), '09:00', true, false],
            [$monday->toDateString(), '09:30', true, true],
        ]);

    StaffAbsence::factory()->for($doctor)->create([
        'type' => 'period', 'start_date' => $monday->subDay()->toDateString(), 'end_date' => $monday->addDay()->toDateString(),
    ]);

    // An absence recorded after the booking hid the appointment, so nobody moved it.
    actingAs($admin, 'admin')
        ->getJson("/api/staff-availability-week/{$doctor->id}?start={$monday->toDateString()}")
        ->assertOk()
        ->assertExactJson([[
            'date' => $monday->toDateString(), 'time' => '09:30', 'is_available' => false, 'is_booked' => true, 'reception_id' => $reception->id,
        ]]);

    actingAs($admin, 'admin')
        ->getJson("/api/staff-availability-week/{$doctor->id}?start=next-week")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('start');
});

it('answers 404, not a database error, to an id that is not a number in the booking URLs', function (string $url): void {
    // The week and the doctors of a service took a plain int: "abc" was a TypeError, a 500.
    actingAs(adminWithPermissions('view_appointment_receptions', 'view_reception_doctor_calendar'), 'admin')
        ->get($url)
        ->assertNotFound();
})->with([
    'the week of a doctor' => '/api/staff-availability-week/abc?start=2026-11-16',
    'the doctors of a service' => '/api/staff-by-service/abc',
    'the free slots of a doctor' => '/api/staff-availability-today/abc',
    'the booked slots of a doctor' => '/api/staff-booked-list-today/abc',
    'the calendar of a doctor' => '/dashboard/receptions-doctor-calendar/abc/1',
    'the calendar of a service' => '/dashboard/receptions-doctor-calendar/1/abc',
]);

it('keeps the appointments of a day without reception hours, and of a dismissed doctor, in the calendars', function (): void {
    $doctor = SchedulingFixtures::doctor(20);
    $reception = SchedulingFixtures::appointment($doctor, '09:00', '09:20');
    $bookedTimes = fn (): array => collect($doctor->fresh()->bookingsOn(today()))->where('is_booked', true)->pluck('reception_id', 'time')->all();
    $week = fn (): array => collect(AppointmentCalendar::of($doctor->fresh(), today(), today())->days(today(), today()))
        ->where('is_booked', true)->map(fn (array $slot): array => [$slot['time'], $slot['is_available'], $slot['reception_id']])->values()->all();

    StaffSchedule::query()->whereBelongsTo($doctor)->where('type', ScheduleType::Reception)->delete();

    expect($doctor->fresh()->availabilityOn(today()))->toBe([])
        ->and($bookedTimes())->toBe(['09:00' => $reception->id])
        ->and($week())->toBe([['09:00', false, $reception->id]]);

    StaffDocument::query()->whereBelongsTo($doctor)->update(['is_work' => false]);

    expect($doctor->fresh()->availabilityOn(today()))->toBe([])
        ->and($bookedTimes())->toBe(['09:00' => $reception->id])
        ->and($week())->toBe([['09:00', false, $reception->id]]);
});

it('offers only employed doctors with an appointment length for a service', function (): void {
    $doctor = SchedulingFixtures::doctor();
    $serviceId = $doctor->services()->value('services.id');

    $withoutLength = SchedulingFixtures::doctor();
    $withoutLength->services()->sync([$serviceId]);
    StaffProfessionalData::query()->whereBelongsTo($withoutLength)->update(['admission_period' => 0]);

    $dismissed = SchedulingFixtures::doctor();
    $dismissed->services()->sync([$serviceId]);
    StaffDocument::query()->whereBelongsTo($dismissed)->update(['is_work' => false]);

    actingAs(adminWithPermissions('view_appointment_receptions'), 'admin')
        ->getJson("/api/staff-by-service/{$serviceId}")
        ->assertOk()
        ->assertExactJson([[
            'id' => $doctor->id,
            'full_name' => $doctor->full_name,
            'admission_period' => 20,
        ]]);
});

it('treats an employee without employment documents as employed everywhere, as sign-in does', function (): void {
    // employed() and the calendar required is_work = true, while isDismissed(), the booking check and
    // sign-in only refused a recorded dismissal: such a doctor could sign in and be booked through
    // the API, but no calendar listed them or drew their grid.
    $doctor = SchedulingFixtures::doctor();
    $serviceId = $doctor->services()->value('services.id');
    StaffDocument::query()->whereBelongsTo($doctor)->delete();
    $dismissed = Staff::factory()->has(StaffDocument::factory()->dismissed(), 'document')->create();
    Staff::factory()->nurse()->create();

    actingAs(adminWithPermissions('view_appointment_receptions', 'create_receptions'), 'admin')
        ->getJson("/api/staff-by-service/{$serviceId}")
        ->assertOk()
        ->assertJsonPath('*.id', [$doctor->id]);

    expect($doctor->fresh()->availabilityOn(today()))->toHaveCount(72)
        ->and(Staff::query()->employed()->orderBy('id')->pluck('id')->all())
        ->toBe(Staff::query()->whereKeyNot($dismissed->id)->orderBy('id')->pluck('id')->all())
        ->and(Staff::query()->dismissed()->pluck('id')->all())->toBe([$dismissed->id]);

    $this->postJson('/api/receptions', SchedulingFixtures::booking($doctor, '10:00', '10:20'))->assertCreated();
});

it('offers a doctor\'s account only the doctor themself for a service', function (Closure $doctorAccount): void {
    // The grid drew a column for every colleague offering the service, whose slots then failed to book.
    $doctorAdmin = $doctorAccount('view_appointment_receptions');
    $serviceId = $doctorAdmin->staff->services()->value('services.id');
    $colleague = SchedulingFixtures::doctor();
    $colleague->services()->attach($serviceId);

    actingAs($doctorAdmin, 'admin')
        ->getJson("/api/staff-by-service/{$serviceId}")
        ->assertOk()
        ->assertExactJson([[
            'id' => $doctorAdmin->staff_id,
            'full_name' => $doctorAdmin->staff->full_name,
            'admission_period' => 20,
        ]]);

    actingAs($doctorAdmin, 'admin')
        ->getJson('/api/staff-by-service/'.$colleague->services()->whereKeyNot($serviceId)->value('services.id'))
        ->assertOk()
        ->assertExactJson([]);
})->with(SchedulingFixtures::doctorAccounts());

it('offers an account without a doctor profile every doctor of a service', function (?int $position): void {
    $doctor = SchedulingFixtures::doctor();
    $serviceId = $doctor->services()->value('services.id');
    $colleague = SchedulingFixtures::doctor();
    $colleague->services()->attach($serviceId);

    $offered = actingAs(SchedulingFixtures::account($position, 'view_appointment_receptions'), 'admin')
        ->getJson("/api/staff-by-service/{$serviceId}")
        ->assertOk()
        ->json('*.id');

    expect(collect($offered)->sort()->values()->all())->toBe([$doctor->id, $colleague->id]);
})->with(SchedulingFixtures::WITHOUT_DOCTOR_PROFILE);

dataset('slots of a calendar', [
    'today\'s free slots' => fn (int $staffId): string => "/api/staff-availability-today/{$staffId}",
    'today\'s booked slots' => fn (int $staffId): string => "/api/staff-booked-list-today/{$staffId}",
    'the week' => fn (int $staffId): string => "/api/staff-availability-week/{$staffId}",
]);

it('shows a doctor\'s account only the doctor\'s own slots', function (Closure $url, Closure $doctorAccount): void {
    $doctorAdmin = $doctorAccount('view_appointment_receptions', 'view_reception_doctor_calendar');
    $colleague = SchedulingFixtures::doctor();

    actingAs($doctorAdmin, 'admin')
        ->getJson($url($colleague->id))
        ->assertForbidden()
        ->assertJsonPath('message', 'Врач работает только со своим календарём приёмов.');

    actingAs($doctorAdmin, 'admin')->getJson($url($doctorAdmin->staff_id))->assertOk();
})->with('slots of a calendar')->with(SchedulingFixtures::doctorAccounts());

it('shows an account without a doctor profile the slots of any employee', function (Closure $url, ?int $position): void {
    $account = SchedulingFixtures::account($position, 'view_appointment_receptions', 'view_reception_doctor_calendar');

    actingAs($account, 'admin')->getJson($url(SchedulingFixtures::doctor()->id))->assertOk();
})->with('slots of a calendar')->with(SchedulingFixtures::WITHOUT_DOCTOR_PROFILE);

it('keeps the availability endpoints behind the booking permission', function (): void {
    $doctor = SchedulingFixtures::doctor();

    actingAs(Admin::factory()->create(), 'admin')
        ->getJson("/api/staff-availability-today/{$doctor->id}")
        ->assertForbidden();

    actingAs(adminWithPermissions('view_appointment_receptions'), 'admin')
        ->getJson("/api/staff-availability-today/{$doctor->id}")
        ->assertOk()
        ->assertJsonCount(72);
});
