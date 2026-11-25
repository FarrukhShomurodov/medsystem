<?php

namespace Database\Factories;

use App\Models\DangerousCombination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Prescription warnings match drug1_ru/drug2_ru against medication names exactly.
 *
 * @extends Factory<DangerousCombination>
 */
class DangerousCombinationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'drug1_en' => 'Warfarin',
            'drug1_ru' => 'Варфарин',
            'drug2_en' => 'Ibuprofen',
            'drug2_ru' => 'Ибупрофен',
            'severity_en' => 'Major',
            'severity_ru' => 'Высокая',
            'mechanism_en' => 'Additive anticoagulant and antiplatelet effect',
            'mechanism_ru' => 'Суммирование антикоагулянтного и антиагрегантного действия',
            'comment_ru' => 'Повышен риск желудочно-кишечного кровотечения.',
        ];
    }
}
