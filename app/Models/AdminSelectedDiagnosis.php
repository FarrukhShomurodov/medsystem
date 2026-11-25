<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminSelectedDiagnosis extends Model
{
    protected $fillable = [
        'admin_id',
        'diagnosis_id',
    ];

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * @return BelongsTo<Diagnosis, $this>
     */
    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }
}
