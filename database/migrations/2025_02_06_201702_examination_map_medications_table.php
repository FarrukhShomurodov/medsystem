<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('examination_map_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_map_id')->constrained('examination_maps')->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('dosage');
            $table->string('admission_period');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_service_to_medications_assignment');
    }
};
