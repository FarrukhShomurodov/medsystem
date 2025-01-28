<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AnalyzeMethod extends Model
{
    protected $fillable = [
        'name'
    ];

    public function analyzes(): belongsToMany
    {
        return $this->belongsToMany(Analyze::class, 'analyze_to_methods_assignment', 'analyze_method_id', 'analyze_id');

    }
}
