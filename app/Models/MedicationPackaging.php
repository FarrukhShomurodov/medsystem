<?php

namespace App\Models;

use App\Enums\PackagingType;
use Illuminate\Database\Eloquent\Model;

/**
 * @property PackagingType $packaging_type
 */
class MedicationPackaging extends Model
{
    protected $fillable = [
        'warehouse_accounting_id',
        'packaging_type',
        'quantity_per_package',
        'package_size',
        'packaging_date',
    ];

    protected function casts(): array
    {
        return [
            'packaging_type' => PackagingType::class,
        ];
    }
}
