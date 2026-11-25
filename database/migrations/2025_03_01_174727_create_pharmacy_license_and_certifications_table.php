<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_license_and_certifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->string('license_number');
            $table->date('license_issue_date');
            $table->date('license_expiry_date');
            $table->string('quality_certificate_number')->nullable();
            $table->text('compliance_standards')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_license_and_certifications');
    }
};
