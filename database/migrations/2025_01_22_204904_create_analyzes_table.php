<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyzes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_map_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('laboratory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->dateTime('analysis_time');
            $table->date('expected_result_date')->nullable();
            //            $table->foreignId('laboratory_assistant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('medical_staff', ['assist', 'doctor'])->nullable();
            $table->longText('target')->nullable();
            $table->enum('status', ['assigned', 'completed', 'received']);
            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyzes');
    }
};
