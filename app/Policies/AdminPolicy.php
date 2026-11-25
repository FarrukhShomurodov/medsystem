<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\Response;

/**
 * Who may change a dashboard account, on top of the edit_admins / delete_admins and edit_staffs /
 * delete_staffs permissions of the admins and staff screens. A main administrator's account holds
 * every permission, so whoever changes its e-mail, password or roles, or deletes it, would take
 * over the whole dashboard: only another main administrator may.
 */
final class AdminPolicy
{
    /** Changing the sign-in data or the roles of the account (the staff form edits those of the employee's login). */
    public function update(Admin $actor, Admin $account): Response
    {
        return $this->mayManage($actor, $account)
            ? Response::allow()
            : Response::deny('Учётную запись главного администратора может изменить только главный администратор.');
    }

    public function delete(Admin $actor, Admin $account): Response
    {
        return $this->mayManage($actor, $account)
            ? Response::allow()
            : Response::deny('Учётную запись главного администратора может удалить только главный администратор.');
    }

    private function mayManage(Admin $actor, Admin $account): bool
    {
        return ! $account->isMainAdmin() || $actor->isMainAdmin();
    }
}
