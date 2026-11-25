<?php

namespace App\Models;

use Database\Factories\SurveyTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyType extends Model
{
    /** @use HasFactory<SurveyTypeFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * @return HasMany<Survey, $this>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
