<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('analyzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_map_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('laboratory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->dateTime('analysis_time');
            $table->date('expected_result_date');
//            $table->foreignId('laboratory_assistant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('medical_staff', ['assist', 'doctor']);
            $table->longText('target');
            $table->enum('status', ['assigned', 'completed', 'received']);
            $table->longText('notes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyzes');
    }
};
