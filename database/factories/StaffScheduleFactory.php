<?php

namespace Database\Factories;

use App\Enums\ScheduleType;
use App\Models\DayOfWeek;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffSchedule>
 */
class StaffScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'day_of_week_id' => DayOfWeek::factory(),
            'type' => ScheduleType::Work,
            'start_time' => '08:00',
            'end_time' => '17:00',
        ];
    }

    /** Hours in which patients can book appointments with the employee. */
    public function reception(): static
    {
        return $this->state(fn (): array => [
            'type' => ScheduleType::Reception,
            'start_time' => '09:00',
            'end_time' => '13:00',
        ]);
    }
}
