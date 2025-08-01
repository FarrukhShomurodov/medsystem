<?php

use App\Models\PatientMedication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('patient_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('examination_map_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('dosage');
            $table->string('admission_period');

            $table->string('single_dose')->nullable();

            $table->enum('status', PatientMedication::STATUS)->default('ordered');

            $table->enum('method', PatientMedication::METHOD);
            $table->enum('admission_rules', PatientMedication::ADMISSION_RULES);
            $table->string('medication_notes')->nullable();

            $table->date('date_of_end');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medications');
    }
};
