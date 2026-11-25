<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_normal_vital_parameters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vital_parameter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->decimal('normal', 8, 1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_normal_vital_parameters');
    }
};
