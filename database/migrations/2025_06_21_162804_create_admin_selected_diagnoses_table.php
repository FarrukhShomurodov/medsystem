<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_selected_diagnoses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diagnosis_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_selected_diagnoses');
    }
};
