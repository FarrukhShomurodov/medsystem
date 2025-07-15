<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('medication_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->string('active_substance_name');
            $table->string('active_substance_dosage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_compositions');
    }
};
