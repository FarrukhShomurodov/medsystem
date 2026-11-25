<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Services, procedures and appointments were billed at the current price of the price list, so a
// price change after ordering changed what the cash desk charged. An order now keeps the price it
// was placed at, as prescriptions already do. Existing orders get the current price: the one they
// were placed at was never recorded, and the current one is what the cash desk charged for them.
// The column is nullable: an order without a price is billed at the price list's (BillableItemLoader).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_services', function (Blueprint $table): void {
            $table->decimal('price', 14, 2)->nullable();
        });

        Schema::table('patient_procedures', function (Blueprint $table): void {
            $table->decimal('price', 14, 2)->nullable();
        });

        Schema::table('receptions', function (Blueprint $table): void {
            $table->decimal('price', 14, 2)->nullable();
        });

        // Only orders without a price are filled in, so running it again never overwrites a kept one.
        DB::statement('UPDATE patient_services SET price = services.price FROM services WHERE services.id = patient_services.service_id AND patient_services.price IS NULL');
        DB::statement('UPDATE patient_procedures SET price = procedures.price FROM procedures WHERE procedures.id = patient_procedures.procedure_id AND patient_procedures.price IS NULL');
        DB::statement('UPDATE receptions SET price = services.price FROM services WHERE services.id = receptions.service_id AND receptions.price IS NULL');
    }

    public function down(): void
    {
        foreach (['patient_services', 'patient_procedures', 'receptions'] as $orders) {
            Schema::table($orders, function (Blueprint $table): void {
                $table->dropColumn('price');
            });
        }
    }
};
