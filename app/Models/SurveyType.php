<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyType extends Model
{
    protected $fillable = ['name'];

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
