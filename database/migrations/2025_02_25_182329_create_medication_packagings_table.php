<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medication_packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->enum('packaging_type', \App\Models\MedicationPackaging::TYPE);
            $table->integer('quantity_per_package');
            $table->integer('package_size');
            $table->string('batch_number');
            $table->date('packaging_date');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('medication_packagings');
    }
};
