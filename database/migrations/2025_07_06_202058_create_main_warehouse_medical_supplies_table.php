<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('main_warehouse_medical_supplies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('main_warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('medical_supplies')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_warehouse_medical_supplies');
    }
};
