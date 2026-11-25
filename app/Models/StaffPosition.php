<?php

namespace App\Models;

use Database\Factories\StaffPositionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

/**
 * A job title. Its permissions live on the spatie role it is linked to by role_id; the login of a
 * staff member holds the role of their position (StaffService), and the role is renamed with it.
 */
class StaffPosition extends Model
{
    /** @use HasFactory<StaffPositionFactory> */
    use HasFactory;

    /** Fixed ids (StaffPositionSeeder). Doctors are the staff patients book appointments with. */
    public const DOCTOR = 1;

    public const HEAD_NURSE = 2;

    public const NURSE = 3;

    public const HEAD_OF_DEPARTMENT = 4;

    /** The front desk: registers patients and confirms what they ordered. */
    public const REGISTRAR = 5;

    public const CASHIER = 6;

    /** The positions seeded with fixed ids; code relies on them, so they can be renamed but not deleted. */
    public const SEEDED_IDS = [
        self::DOCTOR, self::HEAD_NURSE, self::NURSE, self::HEAD_OF_DEPARTMENT, self::REGISTRAR, self::CASHIER,
    ];

    /** Roles granted outside the staff structure; no position may take their name and with it their permissions. */
    public const RESERVED_ROLE_NAMES = [Admin::MAIN_ADMIN_ROLE];

    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSeeded(): bool
    {
        return in_array((int) $this->getKey(), self::SEEDED_IDS, true);
    }
}
