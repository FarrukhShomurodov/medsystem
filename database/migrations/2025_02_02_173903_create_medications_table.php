<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('release_form', \App\Models\Medication::RELEASEFORM);
            $table->string('dosage');
            $table->foreignId('manufacturer_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number');
            $table->date('registration_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
