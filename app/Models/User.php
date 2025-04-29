<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $guard_name = 'user';

    protected $fillable = [
        'login',
        'password',
        'staff_id',
    ];

    protected $guarded = 'user';

    protected $hidden = ['password'];
}
