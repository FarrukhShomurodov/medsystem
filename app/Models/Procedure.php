<?php

namespace App\Models;

use Database\Factories\ProcedureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    /** @use HasFactory<ProcedureFactory> */
    use HasFactory;

    protected $fillable = ['name', 'price'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
