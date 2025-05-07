<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_map_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('service_date');
            $table->time('service_time')->nullable();
            $table->enum('place', ['institution', 'cabinet'])->nullable();
            $table->longText('result')->nullable();
            $table->enum('status', ['assigned', 'in_process', 'completed', 'cancelled']);
            $table->json('payment')->nullable();
            $table->json('insurance_policy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_services');
    }
};
