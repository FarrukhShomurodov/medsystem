<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An allergy of the reference list. The table keeps its historical (misspelled) name.
 */
class Allergy extends Model
{
    protected $table = 'allegories';

    protected $fillable = [
        'name',
    ];
}
