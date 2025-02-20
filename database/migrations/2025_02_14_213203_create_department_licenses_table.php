<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('license_number');
            $table->date('expiration_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_licenses');
    }
};
