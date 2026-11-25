<?php

namespace App\Models;

use Database\Factories\StaffDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    /** @use HasFactory<StaffDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'employment_contract_number',
        'employment_contract_date_of_signing',
        'employment_contract_validity_period',

        'dismissal_orders_number',
        'dismissal_orders_date',
        'dismissal_reception_number',
        'dismissal_reception_date',

        'passport_series',

        'INN',
        'SNILS',

        'is_work',
    ];

    protected function casts(): array
    {
        return [
            'is_work' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
