<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('household_materials', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number');
            $table->string('name');
            $table->decimal('entry_price_total');
            $table->decimal('entry_price_unit');
            $table->decimal('entry_amount');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('household_materials');
    }
};
