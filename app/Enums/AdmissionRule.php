<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * When a prescribed drug is taken relative to meals and sleep (patient_medications.admission_rules).
 */
enum AdmissionRule: string implements HasLabel
{
    use HasValues;

    case Anytime = 'anytime';
    case BeforeMeal = 'before_meal';
    case DuringMeal = 'during_meal';
    case AfterMeal = 'after_meal';
    case BeforeSleep = 'before_sleep';
    case EmptyStomach = 'empty_stomach';

    public function label(): string
    {
        return match ($this) {
            self::Anytime => 'Когда угодно',
            self::BeforeMeal => 'Перед едой',
            self::DuringMeal => 'Во время еды',
            self::AfterMeal => 'После еды',
            self::BeforeSleep => 'Перед сном',
            self::EmptyStomach => 'Натощак',
        };
    }
}
