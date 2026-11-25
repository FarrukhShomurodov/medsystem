<?php

namespace App\Exceptions\Patients;

use DomainException;

/**
 * A patient or a stay holds records that must outlive it (see RetainedRecords); nothing was
 * deleted. The message is shown to the user as it is.
 */
final class DeletionRefusedException extends DomainException
{
    /**
     * @param  non-empty-list<string>  $kinds  "оплаченные услуги", ...
     */
    public static function patient(array $kinds): self
    {
        return new self('Пациента нельзя удалить: у него есть '.implode(', ', $kinds).'. Эти записи хранятся как финансовые и медицинские документы.');
    }

    /**
     * @param  non-empty-list<string>  $kinds
     */
    public static function stay(array $kinds): self
    {
        return new self('Карту обследования нельзя удалить: в ней есть '.implode(', ', $kinds).'. Эти записи хранятся как финансовые и медицинские документы.');
    }
}
