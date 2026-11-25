<?php

namespace App\Services;

use App\Exceptions\Scheduling\AccountException;
use App\Models\Admin;
use App\Models\AdminSelectedDiagnosis;
use Illuminate\Support\Facades\DB;

/**
 * Login accounts of the dashboard. Passwords are hashed by the model's "hashed" cast.
 */
class AdminService
{
    /**
     * @param  array{email: string, password: string}  $validated
     */
    public function store(array $validated): Admin
    {
        return Admin::query()->create($validated);
    }

    /**
     * An empty password keeps the current one.
     *
     * @param  array{email: string, password?: string|null}  $validated
     */
    public function update(Admin $admin, array $validated): Admin
    {
        $admin->fill(['email' => $validated['email']]);

        if (filled($validated['password'] ?? null)) {
            $admin->password = $validated['password'];
        }

        $admin->save();

        return $admin;
    }

    /**
     * @throws AccountException
     */
    public function destroy(Admin $admin, Admin $actor): void
    {
        DB::transaction(function () use ($admin, $actor): void {
            $this->ensureDeletable($admin, $actor);

            $admin->delete();
        });
    }

    /**
     * Nobody can delete their own account or the last main administrator who can still sign in,
     * so the dashboard always keeps an administrator. Also checked when a staff record is deleted
     * together with its account. Call it inside the deleting transaction (see isLastMainAdmin()).
     *
     * @throws AccountException
     */
    public function ensureDeletable(Admin $admin, Admin $actor): void
    {
        if ($admin->is($actor)) {
            throw AccountException::ownAccount();
        }

        if ($this->isLastMainAdmin($admin)) {
            throw AccountException::lastMainAdmin();
        }
    }

    /**
     * Whether the account is a main administrator and no other active one is left: deleting it,
     * or dismissing its employee, would leave nobody to manage the accounts.
     *
     * Call it inside the changing transaction. Every main_admin account stays locked until it
     * commits, so two concurrent changes cannot remove the last two; the active ones are counted
     * by a statement run after the lock is taken, so it sees a dismissal committed meanwhile.
     */
    public function isLastMainAdmin(Admin $admin): bool
    {
        if (! $admin->isMainAdmin()) {
            return false;
        }

        $mainAdminIds = Admin::query()->role(Admin::MAIN_ADMIN_ROLE)->orderBy('id')->lockForUpdate()->pluck('id');

        return ! Admin::query()->whereKey($mainAdminIds->diff([$admin->getKey()]))->active()->exists();
    }

    /**
     * Adds the diagnosis to the admin's favourites, or removes it when it is already there.
     */
    public function toggleSelectedDiagnosis(Admin $admin, int $diagnosisId): void
    {
        $attributes = ['admin_id' => $admin->id, 'diagnosis_id' => $diagnosisId];

        $deleted = AdminSelectedDiagnosis::query()->where($attributes)->delete();

        if ($deleted === 0) {
            AdminSelectedDiagnosis::query()->create($attributes);
        }
    }
}
