<?php

use App\Services\Clinical\Scoring\DocumentScoreCalculator;
use App\Services\Clinical\Scoring\MedicalScale;
use App\Services\Clinical\Scoring\PatientDemographics;

/*
| The totals of the scored sheets (documents 2-8 of DocumentSeeder) at the boundaries of the
| score ranges DocumentSeeder defines for them.
*/

/**
 * @param  array<string, string>  $answers
 */
function clinicalScore(MedicalScale $scale, array $answers, ?PatientDemographics $patient = null): int
{
    return (new DocumentScoreCalculator)->calculate($scale, $answers, $patient ?? new PatientDemographics(40, false));
}

/**
 * @return array<string, string>
 */
function morseAnswers(
    string $fell = 'Нет',
    string $disease = 'Нет',
    string $walking = 'Сам / постельный режим',
    string $infusion = 'Нет',
    string $gait = 'Нормальная (ходит свободно)',
    string $mind = 'Осознает свою способность двигаться',
): array {
    return [
        'Падал ли в последние 3 мес.?' => $fell,
        'Есть ли сопутствующее заболевание? (со стороны сердечно-сосудистой, костно-суставной и мышечной, нервной систем, судорожный синдром, нарушение зрения, анемия)' => $disease,
        'Ходит самостоятельно' => $walking,
        'Принимает внутривенное вливание (есть система) / принимает гепарин' => $infusion,
        'Походка' => $gait,
        'Психическое состояние' => $mind,
    ];
}

/**
 * @return array<string, string>
 */
function humptyDumptyAnswers(
    string $diagnosis = 'Другое',
    string $cognition = 'Ориентируется в способностях',
    string $environment = 'Ходит самостоятельно',
    string $surgery = 'Более 48 ч / не проводилось',
    string $medication = 'Не принимает / другие',
): array {
    return [
        'Диагноз' => $diagnosis,
        'Когнитивные расстройства' => $cognition,
        'Факторы среды' => $environment,
        'Состояние после операции/седации/анестезии/инвазивной процедуры' => $surgery,
        'Медикаменты' => $medication,
    ];
}

/**
 * @return array<string, string>
 */
function outpatientAnswers(int $yes, string $yesValue = '1'): array
{
    $questions = [
        'Падали ли вы в последние 3 мес.? (При повторной оценке: падал/а ли за последние сутки?)',
        'Чувствуете ли вы сейчас головокружение?',
        'Чувствуете слабость в ногах?',
        'Ходит с костылями/тростью/в инвалидном кресле',
        'Принял процедуру, медикамент или анестезию, повышающую риск падения или риск головокружения',
    ];

    return array_combine($questions, array_map(fn (int $i): string => $i < $yes ? $yesValue : '0', array_keys($questions)));
}

/**
 * @param  list<int>  $points  points of face, legs, activity, cry, consolability
 * @return array<string, string>
 */
function flaccAnswers(array $points): array
{
    return array_combine(
        ['Лицо (Face)', 'Ноги (Legs)', 'Активность (Activity)', 'Плач (Cry)', 'Успокаиваемость (Consolability)'],
        array_map(fn (int $point): string => "{$point} - вариант", $points),
    );
}

/**
 * @param  list<int>  $points  points of face, cry, breathing, arms, legs, arousal
 * @return array<string, string>
 */
function nipsAnswers(array $points): array
{
    return array_combine(
        ['Выражение лица', 'Крик / плач', 'Характер дыхания', 'Руки', 'Ноги', 'Состояние возбуждения'],
        array_map(fn (int $point): string => "{$point} - вариант", $points),
    );
}

/**
 * @return array<string, string>
 */
function cpotAnswers(int $face, int $body, int $ventilator, int $vocalization, int $muscles, string $interpretation = '6-8 - боль есть, рассмотреть медикаментозную терапию'): array
{
    return [
        'Выражение лица (Facial expressions)' => "{$face} - вариант",
        'Движения тела (Body movements)' => "{$body} - вариант",
        'Толерантность к ИВЛ (для интубированных)' => "{$ventilator} - вариант",
        'Издавание звуков (для неинтубированных)' => "{$vocalization} - вариант",
        'Напряжение мышц (Muscle tension)' => "{$muscles} - вариант",
        'Интерпретация баллов CPOT' => $interpretation,
    ];
}

describe('Morse fall risk', function (): void {
    it('scores a patient without risk factors 0', function (): void {
        expect(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers()))->toBe(0);
    });

    it('weights every risk factor as the Morse scale does', function (array $answers, int $score): void {
        expect(clinicalScore(MedicalScale::MorseFallRisk, $answers))->toBe($score);
    })->with([
        'fall in the last 3 months' => [morseAnswers(fell: 'Да'), 25],
        'secondary diagnosis' => [morseAnswers(disease: 'Да'), 15],
        'crutches or walker' => [morseAnswers(walking: 'Костыли / ходунки / трость'), 15],
        'holds on to furniture' => [morseAnswers(walking: 'Опора о мебель или стены'), 30],
        'IV line or heparin' => [morseAnswers(infusion: 'Да'), 20],
        'weak gait' => [morseAnswers(gait: 'Слегка несвободная (ходит с остановками, шаги короткие, иногда с задержкой)'), 10],
        'impaired gait' => [morseAnswers(gait: 'Нарушения (не может встать, ходит опираясь, смотрит вниз)'), 20],
        'forgets own limitations' => [morseAnswers(mind: 'Не знает или забывает, что нужна помощь при движении'), 15],
    ]);

    it('puts the range boundaries 24/25 and 50/51 where the answers say', function (): void {
        expect(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers(disease: 'Да', gait: 'Слегка несвободная (ходит с остановками, шаги короткие, иногда с задержкой)')))->toBe(25)
            ->and(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers(infusion: 'Да')))->toBe(20)
            ->and(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers(fell: 'Да', infusion: 'Да')))->toBe(45)
            ->and(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers(fell: 'Да', walking: 'Костыли / ходунки / трость', gait: 'Слегка несвободная (ходит с остановками, шаги короткие, иногда с задержкой)')))->toBe(50)
            ->and(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers(fell: 'Да', walking: 'Опора о мебель или стены')))->toBe(55);
    });

    it('reaches 125 with every risk factor', function (): void {
        expect(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers(
            fell: 'Да',
            disease: 'Да',
            walking: 'Опора о мебель или стены',
            infusion: 'Да',
            gait: 'Нарушения (не может встать, ходит опираясь, смотрит вниз)',
            mind: 'Не знает или забывает, что нужна помощь при движении',
        )))->toBe(125);
    });

    it('ignores questions that are not part of the scale', function (): void {
        expect(clinicalScore(MedicalScale::MorseFallRisk, morseAnswers(fell: 'Да') + ['Комментарий' => '30']))->toBe(25);
    });
});

describe('Humpty Dumpty fall risk', function (): void {
    it('takes age and sex from the patient record', function (int $age, bool $isMale, int $score): void {
        expect(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(), new PatientDemographics($age, $isMale)))
            ->toBe($score);
    })->with([
        'girl of 13: lowest possible total' => [13, false, 7],
        'girl of 12' => [12, false, 8],
        'girl of 7' => [7, false, 8],
        'girl of 6' => [6, false, 9],
        'girl of 3' => [3, false, 9],
        'girl of 2' => [2, false, 10],
        'newborn girl' => [0, false, 10],
        'newborn boy' => [0, true, 11],
    ]);

    it('scores every item of the sheet', function (): void {
        $teenageGirl = new PatientDemographics(15, false);

        expect(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(diagnosis: 'Неврологические нарушения'), $teenageGirl))->toBe(10)
            ->and(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(cognition: 'Не осознает ограничений'), $teenageGirl))->toBe(9)
            ->and(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(environment: 'Падения в анамнезе'), $teenageGirl))->toBe(10)
            ->and(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(surgery: 'До 24 ч'), $teenageGirl))->toBe(9)
            ->and(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(medication: '2 и более из указанных групп'), $teenageGirl))->toBe(9);
    });

    it('puts the low/high boundary between 11 and 12', function (): void {
        $boyOfFive = new PatientDemographics(5, true);

        expect(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(diagnosis: 'Психологические/поведенческие расстройства'), $boyOfFive))->toBe(11)
            ->and(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(diagnosis: 'Нарушения оксигенации'), $boyOfFive))->toBe(12);
    });

    it('reaches 23 for a boy under 3 with every risk factor', function (): void {
        expect(clinicalScore(MedicalScale::HumptyDumptyFallRisk, humptyDumptyAnswers(
            diagnosis: 'Неврологические нарушения',
            cognition: 'Не осознает ограничений',
            environment: 'Падения в анамнезе',
            surgery: 'До 24 ч',
            medication: '2 и более из указанных групп',
        ), new PatientDemographics(2, true)))->toBe(23);
    });

    it('ignores the age and sex questions older sheets still ask', function (): void {
        $answers = ['Возраст' => 'Младше 3-х лет', 'Пол' => 'Мужской'] + humptyDumptyAnswers();

        expect(clinicalScore(MedicalScale::HumptyDumptyFallRisk, $answers, new PatientDemographics(15, false)))->toBe(7);
    });
});

describe('outpatient fall risk (КДО)', function (): void {
    it('gives one point per yes', function (int $yes): void {
        expect(clinicalScore(MedicalScale::OutpatientFallRisk, outpatientAnswers($yes)))->toBe($yes);
    })->with([0, 1, 2, 3, 4, 5]);

    it('reads the yes of the document checkbox, the sheet options and the survey forms', function (string $yes): void {
        expect(clinicalScore(MedicalScale::OutpatientFallRisk, outpatientAnswers(3, $yes)))->toBe(3);
    })->with(['1', 'Да', 'yes']);

    it('does not count no answers', function (string $no): void {
        expect(clinicalScore(MedicalScale::OutpatientFallRisk, outpatientAnswers(5, $no)))->toBe(0);
    })->with(['0', 'Нет', 'no', '']);
});

describe('pain', function (): void {
    it('takes the numeric pain intensity, not the first question by position', function (string $intensity, int $score): void {
        $answers = [
            'Локализация боли' => 'поясница',
            'Интенсивность боли (0-10)' => $intensity,
            'Характер боли' => 'Н - ноющая',
        ];

        expect(clinicalScore(MedicalScale::NumericPain, $answers))->toBe($score);
    })->with([
        ['0 - Боли нет', 0],
        ['1 - Очень слабая боль', 1],
        ['8 - Жестокая боль', 8],
        ['10 - Максимально возможная боль', 10],
    ]);

    it('sums the FLACC items: 0 calm, 1-3, 4-6, 7-10', function (array $points, int $score): void {
        expect(clinicalScore(MedicalScale::FlaccPain, flaccAnswers($points)))->toBe($score);
    })->with([
        [[0, 0, 0, 0, 0], 0],
        [[1, 0, 0, 0, 0], 1],
        [[1, 1, 1, 0, 0], 3],
        [[1, 1, 1, 1, 0], 4],
        [[2, 2, 2, 0, 0], 6],
        [[2, 2, 2, 1, 0], 7],
        [[2, 2, 2, 2, 2], 10],
    ]);

    it('sums the NIPS items: 0-3 unlikely, 4-7 likely', function (array $points, int $score): void {
        expect(clinicalScore(MedicalScale::NipsPain, nipsAnswers($points)))->toBe($score);
    })->with([
        [[0, 0, 0, 0, 0, 0], 0],
        [[1, 2, 0, 0, 0, 0], 3],
        [[1, 2, 1, 0, 0, 0], 4],
        [[1, 2, 1, 1, 1, 1], 7],
    ]);

    it('sums the CPOT items with one of ventilator compliance and vocalization: 0-1, 2-5, 6-8', function (array $points, int $score): void {
        expect(clinicalScore(MedicalScale::CpotPain, cpotAnswers(...$points)))->toBe($score);
    })->with([
        [[0, 0, 0, 0, 0], 0],
        [[1, 0, 0, 0, 0], 1],
        [[1, 1, 0, 0, 0], 2],
        [[1, 1, 1, 1, 2], 5],
        [[2, 1, 1, 0, 2], 6],
        [[2, 2, 2, 2, 2], 8],
        [[0, 0, 2, 0, 0], 2],
        [[0, 0, 0, 2, 0], 2],
    ]);

    it('does not score the CPOT interpretation question', function (): void {
        expect(clinicalScore(MedicalScale::CpotPain, cpotAnswers(0, 0, 0, 0, 0, interpretation: '6-8 - боль есть')))->toBe(0);
    });
});
