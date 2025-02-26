<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medication_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->string('indications');
            $table->string('administration_method');
            $table->string('dosage_regimen');
            $table->string('contraindications');
            $table->string('usage_features');
            $table->string('side_effects');
            $table->string('drug_interactions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_applications');
    }
};
