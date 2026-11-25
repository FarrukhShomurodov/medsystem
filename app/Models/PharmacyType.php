<?php

namespace App\Models;

use Database\Factories\PharmacyTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyType extends Model
{
    /** @use HasFactory<PharmacyTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
