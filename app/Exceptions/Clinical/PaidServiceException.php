<?php

namespace App\Exceptions\Clinical;

class PaidServiceException extends ClinicalException
{
    public static function reassigned(): self
    {
        return new self('Услуга уже оплачена: сменить услугу или пациента нельзя.');
    }

    public static function deleted(): self
    {
        return new self('Оплаченную услугу нельзя удалить.');
    }
}
