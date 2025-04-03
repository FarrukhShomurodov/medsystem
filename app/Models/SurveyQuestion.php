<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends Model
{
    protected $fillable = [
        'survey_id',
        'question',
        'type',
        'options',
        'group_id',
    ];

    protected $casts = [
        'options' => 'json',
    ];

    const TYPE = ['choice', 'boolean', 'text'];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SurveyGroup::class);
    }
}
