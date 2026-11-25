<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());
        // Patient names follow Russian conventions (gendered surname and patronymic) whatever APP_FAKER_LOCALE is.
        $russian = fake('ru_RU');

        return [
            'first_name' => $russian->firstName($gender->value),
            'last_name' => $russian->lastName($gender->value),
            'middle_name' => $russian->format('middleName', [$gender->value]),
            'birth_date' => fake()->dateTimeBetween('-85 years', '-1 year')->format('Y-m-d'),
            'gender' => $gender,
            'citizenship' => 'Узбекистан',
            'passport_series' => fake()->regexify('[A-Z]{2}[0-9]{7}'),
            'phone' => fake()->numerify('+998 9# ###-##-##'),
            'email' => fake()->optional(0.3)->safeEmail(),
            'residential_address_street' => $russian->streetName(),
            'residential_address_house' => (string) fake()->numberBetween(1, 120),
        ];
    }

    public function insured(): static
    {
        return $this->state(fn (): array => [
            'insurance_policy_number' => fake()->numerify('ДМС-########'),
            'insurance_company' => fake()->randomElement(['СК «Надёжность»', 'СК «МедПолис»', 'СК «Опора»']),
            'insurance_type' => 'ДМС',
            'insurance_expiry_date' => fake()->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
        ]);
    }
}
