<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medication_license_and_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->string('sales_license');
            $table->string('quality_certificate');
            $table->string('clinical_trials');
            $table->string('registration_documents');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('medication_license_and_certifications');
    }
};
