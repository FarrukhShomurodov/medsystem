<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_professional_data', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('staff_positions')->cascadeOnDelete();
            $table->foreignId('addition_position_id')->nullable()->constrained('staff_positions')->cascadeOnDelete();
            $table->foreignId('speciality_id');
            $table->foreignId('department_id');

            $table->enum('category', ['first', 'second'])->nullable();

            $table->string('education_name')->nullable();
            $table->date('education_graduation_year')->nullable();

            $table->string('certificate_name')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('certificate_issue_date')->nullable();
            $table->date('certificate_validity_period')->nullable();

            $table->integer('admission_period')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_professional_data');
    }
};
