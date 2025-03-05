<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('examination_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->date('admission_date');
            $table->date('discharge_date')->nullable();
            $table->string('admission_diagnosis_code')->nullable();
            $table->longText('admission_diagnosis_description')->nullable();
            $table->string('discharge_diagnosis_code')->nullable();
            $table->longText('discharge_diagnosis_description')->nullable();
            $table->longText('examination_purpose')->nullable();
            $table->longText('complaints')->nullable();
            $table->longText('general_anamnesises')->nullable();
            $table->longText('disease_history')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_maps');
    }
};
