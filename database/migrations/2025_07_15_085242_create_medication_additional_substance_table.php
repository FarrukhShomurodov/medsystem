<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_additional_substance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_composition_id')->constrained()->cascadeOnDelete();
            $table->string('substance_name');
            $table->string('substance_dosage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_additional_substance');
    }
};
