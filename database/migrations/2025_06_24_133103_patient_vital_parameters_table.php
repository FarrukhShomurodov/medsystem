<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_vital_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vital_parameter_id')->constrained()->cascadeOnDelete();
            $table->integer('value');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('patient_vital_parameters');
    }
};
