<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('employment_contract_number');
            $table->string('employment_contract_date_of_signing');
            $table->string('employment_contract_validity_period');
            $table->string('dismissal_orders_number');
            $table->string('dismissal_orders_date');
            $table->string('dismissal_reception_number');
            $table->string('dismissal_reception_date');
            $table->string('passport_series');
            $table->string('INN');
            $table->string('SNILS');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
    }
};
