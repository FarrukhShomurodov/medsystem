<?php

namespace App\Exceptions\Clinical;

use App\Enums\ServiceStatus;

class ServiceStatusTransitionException extends ClinicalException
{
    public static function between(ServiceStatus $from, ServiceStatus $to): self
    {
        return new self("Нельзя сменить статус услуги «{$from->label()}» на «{$to->label()}».");
    }
}
