<?php

namespace App\Queries\Billing;

use App\Models\Patient;

/**
 * The orders of a day waiting for the registrar, of all patients (RegistrarBasketQuery::queue()).
 */
final readonly class RegistrarQueue
{
    /**
     * @param  int  $orders  how many orders wait
     * @param  int  $patients  how many patients they belong to
     * @param  list<array{patient: Patient, orders: int}>  $first  the patients with the most of them, most first
     */
    public function __construct(
        public int $orders,
        public int $patients,
        public array $first,
    ) {}
}
