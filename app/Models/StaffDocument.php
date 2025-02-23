<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDocument extends Model
{
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
    ];
}
