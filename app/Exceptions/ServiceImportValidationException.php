<?php

namespace App\Exceptions;

use Exception;

class ServiceImportValidationException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
