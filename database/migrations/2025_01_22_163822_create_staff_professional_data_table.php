<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_professional_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id');
            $table->foreignId('speciality_id');
            $table->foreignId('department_id');

            $table->enum('category', ['first', 'second'])->nullable();

            $table->string('education_name');
            $table->date('education_graduation_year');

            $table->string('certificate_name');
            $table->string('certificate_number');
            $table->date('certificate_issue_date');
            $table->date('certificate_validity_period');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_professional_data');
    }
};
