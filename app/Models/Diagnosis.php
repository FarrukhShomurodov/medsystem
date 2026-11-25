<?php

namespace App\Models;

use Database\Factories\DiagnosisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagnosis extends Model
{
    /** @use HasFactory<DiagnosisFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * @return HasMany<Survey, $this>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
