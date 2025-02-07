<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('patient_service_to_medications_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_service_id')->constrained('patient_services')->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('dosage');
            $table->string('admission_period');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_service_to_medications_assignment');
    }
};
