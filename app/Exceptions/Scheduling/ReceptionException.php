<?php

namespace App\Exceptions\Scheduling;

use App\Enums\ReceptionStatus;
use DomainException;

/**
 * An appointment that cannot be booked or moved to the requested status. The message is shown
 * to the user as it is.
 */
final class ReceptionException extends DomainException
{
    public static function slotTaken(): self
    {
        return new self('Это время уже занято: выберите другой слот.');
    }

    public static function lengthMismatch(int $minutes): self
    {
        return new self("Приём у этого сотрудника длится {$minutes} мин.: обновите календарь и выберите время заново.");
    }

    public static function offGrid(int $minutes): self
    {
        return new self("Это время не попадает в сетку приёма сотрудника (слоты по {$minutes} мин.): обновите календарь и выберите время заново.");
    }

    public static function serviceNotOffered(): self
    {
        return new self('Сотрудник не ведёт приём по этой услуге.');
    }

    public static function outsideReceptionHours(): self
    {
        return new self('Это время вне часов приёма сотрудника: выберите слот в календаре.');
    }

    public static function staffAbsent(): self
    {
        return new self('Сотрудник отсутствует в этот день.');
    }

    public static function staffDismissed(): self
    {
        return new self('Сотрудник уволен, запись к нему невозможна.');
    }

    public static function transitionNotAllowed(ReceptionStatus $from, ReceptionStatus $to): self
    {
        return new self("Приём в статусе «{$from->label()}» нельзя перевести в статус «{$to->label()}».");
    }
}
