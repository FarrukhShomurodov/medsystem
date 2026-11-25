<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_inspections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('bp');
            $table->string('pulse');
            $table->string('saturation');
            $table->string('temperature');
            $table->string('chdd');
            $table->string('consciousness_level');
            $table->string('oxygen_status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_inspections');
    }
};
