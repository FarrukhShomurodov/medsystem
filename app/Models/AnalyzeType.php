<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AnalyzeType extends Model
{
    protected $fillable = [
        'name'
    ];

    public function analyzes(): BelongsToMany
    {
        return $this->belongsToMany(Analyze::class, 'analyze_to_type_assignment', 'analyze_id', 'type_id');
    }
}
