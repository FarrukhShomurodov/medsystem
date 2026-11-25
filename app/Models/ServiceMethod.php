<?php

namespace App\Models;

use Database\Factories\ServiceMethodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMethod extends Model
{
    /** @use HasFactory<ServiceMethodFactory> */
    use HasFactory;

    protected $fillable = ['name'];
}
