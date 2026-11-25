<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_packagings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_accounting_id')->constrained()->cascadeOnDelete();
            $table->enum('packaging_type', ['блистер', 'флакон', 'тюбик']);
            $table->integer('quantity_per_package');
            $table->integer('package_size');
            $table->date('packaging_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_packagings');
    }
};
