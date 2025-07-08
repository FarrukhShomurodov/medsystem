<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('main_warehouse_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->date('expiration_date');
            $table->date('date_of_receipt');
            $table->string('batch_number');

            $table->integer('retail_price')->nullable();
            $table->integer('wholesale_price')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->cascadeOnDelete();

            $table->enum('packaging_type', \App\Models\MedicationPackaging::TYPE)->nullable();
            $table->integer('quantity_per_package')->nullable();
            $table->integer('package_size')->nullable();
            $table->date('packaging_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_warehouse_medications');
    }
};
