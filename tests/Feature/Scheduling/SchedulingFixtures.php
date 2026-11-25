<?php

namespace Tests\Feature\Scheduling;

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\StaffProfessionalData;
use App\Models\StaffSchedule;
use Carbon\CarbonInterface;
use Closure;
use Database\Seeders\DayOfWeekSeeder;
use Database\Seeders\StaffPositionSeeder;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\seed;

/**
 * Doctors that can be booked, and the logins that book them, for the scheduling tests.
 */
final class SchedulingFixtures
{
    /**
     * The accounts without a doctor profile, which work on every employee's calendar, as a dataset
     * of position ids for account().
     */
    public const array WITHOUT_DOCTOR_PROFILE = [
        'an administrator without an employee' => [null],
        'a registrar' => [StaffPosition::REGISTRAR],
        'a cashier' => [StaffPosition::CASHIER],
        'a nurse' => [StaffPosition::NURSE],
    ];

    /**
     * An employed doctor offering an appointment service, with working and reception hours on the
     * weekday of the date (today by default).
     *
     * @param  list<array{string, string}>  $receptionHours
     */
    public static function doctor(int $slotMinutes = 20, array $receptionHours = [['09:00', '13:00']], ?CarbonInterface $on = null): Staff
    {
        self::seedWeekdays();

        $doctor = Staff::factory()
            ->doctor(['admission_period' => $slotMinutes])
            ->hasAttached(Service::factory()->appointment(), relationship: 'services')
            ->create();

        $day = ($on ?? today())->isoWeekday();

        StaffSchedule::factory()->for($doctor)->create(['day_of_week_id' => $day, 'start_time' => '08:00', 'end_time' => '18:00']);

        foreach ($receptionHours as [$start, $end]) {
            StaffSchedule::factory()->for($doctor)->reception()->create(['day_of_week_id' => $day, 'start_time' => $start, 'end_time' => $end]);
        }

        return $doctor;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function appointment(Staff $doctor, string $start, string $end, array $attributes = []): Reception
    {
        return Reception::factory()->create([
            'staff_id' => $doctor->id,
            'service_id' => $doctor->services()->value('services.id'),
            'start_time' => $start,
            'end_time' => $end,
            ...$attributes,
        ]);
    }

    /**
     * What the booking modal posts for the doctor.
     *
     * @return array<string, mixed>
     */
    public static function booking(Staff $doctor, string $start, string $end, array $overrides = []): array
    {
        return [
            'patient_id' => Patient::factory()->create()->id,
            'staff_id' => $doctor->id,
            'service_id' => $doctor->services()->value('services.id'),
            'date' => today()->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'new',
            ...$overrides,
        ];
    }

    /**
     * A doctor's login: the employee holds the doctor position (fixed id 1), so the account has a
     * doctor profile (Admin::doctorProfile()).
     */
    public static function doctorAccount(string ...$permissions): Admin
    {
        return self::account(StaffPosition::DOCTOR, ...$permissions);
    }

    /**
     * doctorAccount() as a dataset, also for a doctor who is the main administrator: the doctor
     * position confines an account whatever its roles, as no Gate::before lets a role past a policy.
     *
     * @return array<string, array{Closure(string...): Admin}>
     */
    public static function doctorAccounts(): array
    {
        return [
            'a doctor' => [self::doctorAccount(...)],
            'a doctor who is also the main administrator' => [
                fn (string ...$permissions): Admin => self::doctorAccount(...$permissions)
                    ->assignRole(Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD)),
            ],
        ];
    }

    /**
     * A login holding exactly the given permissions: of an employee in the seeded position, or of no
     * employee at all (null, an account made on the admins screen). The employee takes appointments
     * like doctor(), so only the position decides whether the account has a doctor profile.
     */
    public static function account(?int $positionId, string ...$permissions): Admin
    {
        $admin = adminWithPermissions(...$permissions);

        if ($positionId === null) {
            return $admin;
        }

        seed(StaffPositionSeeder::class);
        $staff = self::doctor();
        StaffProfessionalData::query()->whereBelongsTo($staff)->update(['position_id' => $positionId]);

        return tap($admin)->update(['staff_id' => $staff->id]);
    }

    public static function seedWeekdays(): void
    {
        app(DayOfWeekSeeder::class)->run();
    }
}
