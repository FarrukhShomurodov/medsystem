<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medication_compatibility', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_medication_id')->constrained('patient_medications')->cascadeOnDelete();
            $table->foreignId('compatible_medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('dosage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medication_compatibility');
    }
};
