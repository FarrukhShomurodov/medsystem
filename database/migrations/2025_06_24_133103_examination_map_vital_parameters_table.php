<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('examination_map_vital_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_map_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vital_parameter_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 8, 1);
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('examination_map_vital_parameters');
    }
};
