<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_employes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->integer('doctors_count');
            $table->integer('nurses_count');
            $table->integer('lab_technicians_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_employes');
    }
};
