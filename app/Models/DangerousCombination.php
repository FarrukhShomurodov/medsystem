<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DangerousCombination extends Model
{
    protected $fillable = [
        'drug1_en', 'drug1_ru',
        'drug2_en', 'drug2_ru',
        'severity_en', 'severity_ru',
        'mechanism_en', 'mechanism_ru',
        'comment_en', 'comment_ru',
    ];
}
