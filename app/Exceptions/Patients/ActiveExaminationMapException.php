<?php

namespace App\Exceptions\Patients;

use DomainException;

/**
 * A patient is admitted while another stay of theirs is still open: a patient has one active
 * examination map at a time.
 */
class ActiveExaminationMapException extends DomainException
{
    public function __construct()
    {
        parent::__construct('У пациента уже есть активная карта обследования: завершите её, прежде чем открывать новую.');
    }
}
