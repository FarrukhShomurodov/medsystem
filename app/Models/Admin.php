<?php

namespace App\Models;

use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory, HasRoles;

    /** The auth guard of every dashboard account; roles and permissions are defined for it. */
    public const GUARD = 'admin';

    /** The role that holds every permission (PermissionSeeder). */
    public const MAIN_ADMIN_ROLE = 'main_admin';

    protected string $guard_name = self::GUARD;

    protected $fillable = [
        'email',
        'password',
        'staff_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsToMany<Patient, $this>
     */
    public function lastViewed(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'last_viewed_patients')->withTimestamps();
    }

    /**
     * The employee who signs in with this account; accounts created on the admins screen have none.
     *
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function isMainAdmin(): bool
    {
        return $this->hasRole(self::MAIN_ADMIN_ROLE);
    }

    /**
     * Accounts that may sign in. An employee's login ends with their dismissal; an account
     * without an employee (created on the admins screen) stays active.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereDoesntHave('staff', fn (Builder $staff) => $staff->dismissed());
    }

    public function isActive(): bool
    {
        return $this->staff_id === null || static::query()->whereKey($this->getKey())->active()->exists();
    }

    /**
     * The staff record of a doctor's account, null for everyone else. Doctors see and book only
     * their own appointments (ReceptionPolicy, the reception list). Decided by the doctor position
     * (fixed id), not by the roles: renaming the position or its role does not change who counts as
     * a doctor, and a doctor who is also the main administrator is confined all the same.
     */
    public function doctorProfile(): ?Staff
    {
        $staff = $this->staff;

        return (int) $staff?->professionalData?->position_id === StaffPosition::DOCTOR ? $staff : null;
    }
}
