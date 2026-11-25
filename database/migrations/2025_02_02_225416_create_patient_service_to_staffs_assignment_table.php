<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_service_to_staffs_assignment', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_service_id')->constrained('patient_services')->cascadeOnDelete();
            $table->foreignId('service_staff_id')->constrained('staff')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_service_to_staffs_assignment');
    }
};
