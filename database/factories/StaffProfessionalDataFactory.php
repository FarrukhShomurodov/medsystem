<?php

namespace Database\Factories;

use App\Enums\QualificationCategory;
use App\Models\Department;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\StaffProfessionalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffProfessionalData>
 */
class StaffProfessionalDataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'position_id' => StaffPosition::factory(),
            'speciality_id' => Specialty::factory(),
            'department_id' => Department::factory(),
            'category' => fake()->randomElement(QualificationCategory::cases()),
            'education_name' => 'Ташкентская медицинская академия',
            'education_graduation_year' => fake()->dateTimeBetween('-35 years', '-2 years')->format('Y-m-d'),
            // Length of one appointment slot in minutes (see Staff::availabilityOn()).
            'admission_period' => fake()->randomElement([15, 20, 30]),
        ];
    }
}
