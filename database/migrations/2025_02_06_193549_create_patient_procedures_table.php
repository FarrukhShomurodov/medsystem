<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_procedures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_map_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnDelete();
            $table->enum('status', ['assigned', 'in_process', 'completed', 'cancelled', 'confirmed'])->default('assigned');
            $table->date('procedure_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_procedures');
    }
};
