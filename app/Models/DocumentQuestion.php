<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentQuestion extends Model
{
    protected $fillable = [
        'document_id',
        'question',
        'type',
        'options',
    ];

    protected $casts = [
        'options' => 'json',
    ];

    const TYPE = ['choice', 'boolean', 'text'];

}
