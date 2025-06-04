<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAnswer extends Model
{
    protected $fillable = [
        'document_response_id',
        'question_id',
        'answer',
    ];
}
