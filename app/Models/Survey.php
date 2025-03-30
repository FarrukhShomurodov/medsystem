<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $fillable = [
        'title',
        'survey_type_id',
    ];


    public function type(): BelongsTo
    {
        return $this->belongsTo(SurveyType::class,'survey_type_id','id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }
}
