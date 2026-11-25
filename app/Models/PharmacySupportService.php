<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacySupportService extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'consultation_phone',
        'medication_consultation_available',
    ];

    protected function casts(): array
    {
        return [
            'medication_consultation_available' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Pharmacy, $this>
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
