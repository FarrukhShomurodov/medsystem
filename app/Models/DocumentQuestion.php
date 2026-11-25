<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;

/**
 * @property QuestionType $type
 */
class DocumentQuestion extends Model
{
    protected $fillable = [
        'document_id',
        'question',
        'type',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'options' => 'array',
        ];
    }
}
