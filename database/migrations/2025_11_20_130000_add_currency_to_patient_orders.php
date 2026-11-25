<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Services and appointments keep the price they were ordered at (2025_11_20_120000), but its
// currency was still read from the price list: moving a service to another currency changed the
// amount an earlier order was billed. They now keep the currency too. Procedures have no currency
// of their own and are always billed in the default one.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_services', function (Blueprint $table): void {
            $table->string('currency')->nullable();
        });

        Schema::table('receptions', function (Blueprint $table): void {
            $table->string('currency')->nullable();
        });

        // The code of the price list entry as billing reads it (Service::priceCurrency(): trimmed,
        // upper case, UZS when the entry names none). Only orders without one are filled in, so
        // running it again never overwrites a kept currency.
        DB::statement("UPDATE patient_services SET currency = COALESCE(NULLIF(UPPER(BTRIM(services.currency)), ''), 'UZS') FROM services WHERE services.id = patient_services.service_id AND patient_services.currency IS NULL");
        DB::statement("UPDATE receptions SET currency = COALESCE(NULLIF(UPPER(BTRIM(services.currency)), ''), 'UZS') FROM services WHERE services.id = receptions.service_id AND receptions.currency IS NULL");
    }

    public function down(): void
    {
        Schema::table('patient_services', function (Blueprint $table): void {
            $table->dropColumn('currency');
        });

        Schema::table('receptions', function (Blueprint $table): void {
            $table->dropColumn('currency');
        });
    }
};
