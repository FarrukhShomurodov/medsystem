<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->integer('systolic')->nullable();          // Систолическое давление
            $table->integer('diastolic')->nullable();         // Диастолическое давление
            $table->integer('pulse')->nullable();             // Пульс
            $table->float('temperature')->nullable();         // Температура
            $table->integer('saturation')->nullable();        // Сатурация
            $table->integer('breathing_rate')->nullable();    // ЧДД
            $table->string('consciousness')->nullable();      // Уровень сознания
            $table->string('oxygen_type')->nullable();        // Кислород или воздух
            $table->integer('pain_score')->nullable();        // Оценка боли
            $table->integer('fall_risk')->nullable();         // Риск падения
            $table->integer('urine_balance')->nullable();     // Диурез / баланс
            $table->integer('total_score')->nullable();       // Общий балл
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('patient_parameters');
    }
};
