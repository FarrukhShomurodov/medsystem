<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasRoles;

    protected $guard_name = ['admin'];

    protected $fillable = [
        'email',
        'password',
        'staff_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'password' => 'string',
    ];

    protected $guarded = 'admin';

    protected $hidden = ['password'];

    public function lastViewed(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'last_viewed_patients')->withTimestamps();
    }
}
