<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyzeResult extends Model
{
    protected $fillable = [
        'analyze_id',
        'type_id',
        'unit',
        'value',
        'reference_range',
        'comment',
    ];

    public function analyze(): BelongsTo
    {
        return $this->belongsTo(Analyze::class, 'analyze_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AnalyzeType::class, 'type_id');
    }
}
