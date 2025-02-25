<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'name',
        'last_inspection',
        'status',
    ];

    const STATUS = ['working', 'broken'];
}
