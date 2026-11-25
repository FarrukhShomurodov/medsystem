<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "Paid" was only a status of the order, so the cashier's paid list counted an order on the day of
// the service or appointment, and lost it once the order moved on (carried out, the visit done).
// An order now keeps the moment it was paid at the cash desk. The moment of earlier payments was
// never recorded, so existing rows stay NULL and are counted on the day of the order, as before
// (CashierItemsQuery).
return new class extends Migration
{
    private const array ORDERS = ['patient_services', 'patient_procedures', 'receptions', 'patient_medications'];

    public function up(): void
    {
        foreach (self::ORDERS as $orders) {
            Schema::table($orders, function (Blueprint $table): void {
                $table->timestamp('paid_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (self::ORDERS as $orders) {
            Schema::table($orders, function (Blueprint $table): void {
                $table->dropColumn('paid_at');
            });
        }
    }
};
