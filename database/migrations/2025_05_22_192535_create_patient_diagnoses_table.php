<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_diagnoses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_map_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('diagnosis_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_diagnoses');
    }
};
