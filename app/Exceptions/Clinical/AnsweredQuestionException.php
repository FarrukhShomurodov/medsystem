<?php

namespace App\Exceptions\Clinical;

class AnsweredQuestionException extends ClinicalException
{
    public static function removed(string $question): self
    {
        return new self("Вопрос «{$question}» нельзя удалить: на него уже есть ответы пациентов.");
    }
}
