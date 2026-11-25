<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * The answer a survey or medical document question takes
 * (survey_questions.type and document_questions.type).
 */
enum QuestionType: string implements HasLabel
{
    use HasValues;

    case Boolean = 'boolean';
    case Choice = 'choice';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Boolean => 'Да / Нет',
            self::Choice => 'Список',
            self::Text => 'Текст',
        };
    }
}
