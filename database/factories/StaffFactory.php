<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Staff;
use App\Models\StaffContact;
use App\Models\StaffDocument;
use App\Models\StaffPosition;
use App\Models\StaffProfessionalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());
        // Staff names follow Russian conventions (gendered surname, patronymic) whatever APP_FAKER_LOCALE is.
        $russian = fake('ru_RU');

        return [
            'name' => $russian->firstName($gender->value),
            'last_name' => $russian->lastName($gender->value),
            'middle_name' => $russian->format('middleName', [$gender->value]),
            'date_birthday' => fake()->dateTimeBetween('-65 years', '-23 years')->format('Y-m-d'),
            'gender' => $gender,
            'citizenship' => 'Узбекистан',
        ];
    }

    /**
     * @param  array<string, mixed>  $professionalData
     */
    public function doctor(array $professionalData = []): static
    {
        return $this->withPosition('Доктор', $professionalData);
    }

    /**
     * @param  array<string, mixed>  $professionalData
     */
    public function nurse(array $professionalData = []): static
    {
        return $this->withPosition('Медсестра', $professionalData);
    }

    /**
     * A fully registered employee, as StaffService creates one: contact details, employment
     * documents (currently employed) and professional data with the given position.
     *
     * @param  array<string, mixed>  $professionalData
     */
    public function withPosition(string $position, array $professionalData = []): static
    {
        return $this
            ->has(StaffContact::factory(), 'contact')
            ->has(StaffDocument::factory(), 'document')
            ->has(
                StaffProfessionalData::factory()->state(fn (): array => [
                    'position_id' => StaffPosition::query()->firstOrCreate(['name' => $position])->id,
                    ...$professionalData,
                ]),
                'professionalData',
            );
    }
}
