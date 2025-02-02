<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_service_to_equipments_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_service_id')->constrained('patient_services')->cascadeOnDelete();
            $table->foreignId('service_equipment_id')->constrained('service_equipment')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_service_to_equipments_assignment');
    }
};
