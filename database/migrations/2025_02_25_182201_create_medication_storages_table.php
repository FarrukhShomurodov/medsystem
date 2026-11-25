<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_storages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->integer('storage_temperature');
            $table->string('storage_conditions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_storages');
    }
};
