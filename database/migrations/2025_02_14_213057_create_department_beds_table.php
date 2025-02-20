<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->integer('total_beds');
            $table->integer('occupied_beds');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_beds');
    }
};
