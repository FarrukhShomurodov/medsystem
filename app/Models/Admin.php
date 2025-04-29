<?php

namespace App\Models;

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
}
