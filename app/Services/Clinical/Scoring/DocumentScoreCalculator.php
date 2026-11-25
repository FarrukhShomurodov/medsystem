<?php

namespace App\Services\Clinical\Scoring;

/**
 * Total score of a filled-in assessment sheet.
 *
 * Answers are keyed by the question wording, the identity DocumentSeeder matches questions on.
 * Positions and ids are not stable: older databases keep questions that were later reworded or
 * dropped from a sheet (Humpty Dumpty used to ask for age and sex), and those score nothing here.
 * An answer outside the listed options scores 0; DocumentResponseRequest only accepts listed options.
 */
final class DocumentScoreCalculator
{
    private const array MORSE_POINTS = [
        'Падал ли в последние 3 мес.?' => ['Да' => 25],
        'Есть ли сопутствующее заболевание? (со стороны сердечно-сосудистой, костно-суставной и мышечной, нервной систем, судорожный синдром, нарушение зрения, анемия)' => ['Да' => 15],
        'Ходит самостоятельно' => [
            'Костыли / ходунки / трость' => 15,
            'Опора о мебель или стены' => 30,
        ],
        'Принимает внутривенное вливание (есть система) / принимает гепарин' => ['Да' => 20],
        'Походка' => [
            'Слегка несвободная (ходит с остановками, шаги короткие, иногда с задержкой)' => 10,
            'Нарушения (не может встать, ходит опираясь, смотрит вниз)' => 20,
        ],
        'Психическое состояние' => ['Не знает или забывает, что нужна помощь при движении' => 15],
    ];

    private const array HUMPTY_DUMPTY_POINTS = [
        'Диагноз' => [
            'Неврологические нарушения' => 4,
            'Нарушения оксигенации' => 3,
            'Психологические/поведенческие расстройства' => 2,
            'Другое' => 1,
        ],
        'Когнитивные расстройства' => [
            'Не осознает ограничений' => 3,
            'Забывает об ограничениях' => 2,
            'Ориентируется в способностях' => 1,
        ],
        'Факторы среды' => [
            'Падения в анамнезе' => 4,
            'Вспомогательные средства' => 3,
            'Лежачий / на руках' => 2,
            'Ходит самостоятельно' => 1,
        ],
        'Состояние после операции/седации/анестезии/инвазивной процедуры' => [
            'До 24 ч' => 3,
            '24-48 ч' => 2,
            'Более 48 ч / не проводилось' => 1,
        ],
        'Медикаменты' => [
            '2 и более из указанных групп' => 3,
            '1 медикамент' => 2,
            'Не принимает / другие' => 1,
        ],
    ];

    /** One point per "yes". */
    private const array OUTPATIENT_QUESTIONS = [
        'Падали ли вы в последние 3 мес.? (При повторной оценке: падал/а ли за последние сутки?)',
        'Чувствуете ли вы сейчас головокружение?',
        'Чувствуете слабость в ногах?',
        'Ходит с костылями/тростью/в инвалидном кресле',
        'Принял процедуру, медикамент или анестезию, повышающую риск падения или риск головокружения',
    ];

    private const string PAIN_INTENSITY_QUESTION = 'Интенсивность боли (0-10)';

    /** The options of these questions start with their points ("2 - ..."). */
    private const array FLACC_QUESTIONS = [
        'Лицо (Face)',
        'Ноги (Legs)',
        'Активность (Activity)',
        'Плач (Cry)',
        'Успокаиваемость (Consolability)',
    ];

    private const array NIPS_QUESTIONS = [
        'Выражение лица',
        'Крик / плач',
        'Характер дыхания',
        'Руки',
        'Ноги',
        'Состояние возбуждения',
    ];

    private const array CPOT_QUESTIONS = [
        'Выражение лица (Facial expressions)',
        'Движения тела (Body movements)',
        'Напряжение мышц (Muscle tension)',
    ];

    /**
     * CPOT scores ventilator compliance for intubated patients and vocalization for the others:
     * one item, so the scale stays 0-8. The sheet asks both, so the higher of the two counts.
     */
    private const array CPOT_ALTERNATIVE_QUESTIONS = [
        'Толерантность к ИВЛ (для интубированных)',
        'Издавание звуков (для неинтубированных)',
    ];

    /**
     * @param  array<string, string>  $answers  answer text keyed by question wording
     */
    public function calculate(MedicalScale $scale, array $answers, PatientDemographics $patient): int
    {
        return match ($scale) {
            MedicalScale::MorseFallRisk => $this->optionPoints($answers, self::MORSE_POINTS),
            MedicalScale::HumptyDumptyFallRisk => $this->humptyDumptyAgePoints($patient->ageInYears)
                + ($patient->isMale ? 2 : 1)
                + $this->optionPoints($answers, self::HUMPTY_DUMPTY_POINTS),
            MedicalScale::OutpatientFallRisk => count(array_filter(
                self::OUTPATIENT_QUESTIONS,
                fn (string $question): bool => $this->isYes($answers[$question] ?? ''),
            )),
            MedicalScale::NumericPain => $this->leadingNumber($answers[self::PAIN_INTENSITY_QUESTION] ?? ''),
            MedicalScale::FlaccPain => $this->leadingNumbers($answers, self::FLACC_QUESTIONS),
            MedicalScale::NipsPain => $this->leadingNumbers($answers, self::NIPS_QUESTIONS),
            MedicalScale::CpotPain => $this->leadingNumbers($answers, self::CPOT_QUESTIONS) + max(array_map(
                fn (string $question): int => $this->leadingNumber($answers[$question] ?? ''),
                self::CPOT_ALTERNATIVE_QUESTIONS,
            )),
        };
    }

    private function humptyDumptyAgePoints(int $ageInYears): int
    {
        return match (true) {
            $ageInYears < 3 => 4,
            $ageInYears < 7 => 3,
            $ageInYears < 13 => 2,
            default => 1,
        };
    }

    /**
     * @param  array<string, string>  $answers
     * @param  array<string, array<string, int>>  $points  points of each option, by question
     */
    private function optionPoints(array $answers, array $points): int
    {
        $total = 0;

        foreach ($points as $question => $options) {
            $total += $options[$answers[$question] ?? ''] ?? 0;
        }

        return $total;
    }

    /**
     * @param  array<string, string>  $answers
     * @param  list<string>  $questions
     */
    private function leadingNumbers(array $answers, array $questions): int
    {
        return array_sum(array_map(
            fn (string $question): int => $this->leadingNumber($answers[$question] ?? ''),
            $questions,
        ));
    }

    private function leadingNumber(string $answer): int
    {
        return preg_match('/^\s*(\d+)/', $answer, $matches) === 1 ? (int) $matches[1] : 0;
    }

    /**
     * The document form posts a checkbox as "1"/"0"; the sheet options and the survey forms
     * use "Да" and "yes".
     */
    private function isYes(string $answer): bool
    {
        return in_array(mb_strtolower(trim($answer)), ['1', 'да', 'yes'], true);
    }
}
