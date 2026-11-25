<?php

namespace App\Exceptions\Scheduling;

use DomainException;

/**
 * A change to a login account that would lock people out of the dashboard. The message is shown
 * to the user as it is.
 */
final class AccountException extends DomainException
{
    public static function ownAccount(): self
    {
        return new self('Нельзя удалить учётную запись, под которой вы вошли.');
    }

    public static function lastMainAdmin(): self
    {
        return new self('Нельзя удалить последнего главного администратора.');
    }

    public static function lastMainAdminDismissed(): self
    {
        return new self('Нельзя уволить последнего главного администратора: его учётная запись перестанет работать.');
    }
}
