<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('main_warehouse_household_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_material_id')->constrained('household_materials')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('main_warehouse_household_materials');
    }
};
