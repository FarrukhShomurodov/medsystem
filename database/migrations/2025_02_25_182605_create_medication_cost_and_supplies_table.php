<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medication_cost_and_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_accounting_id')->constrained()->cascadeOnDelete();
            $table->integer('retail_price');
            $table->integer('wholesale_price');
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('medication_cost_and_supplies');
    }
};
