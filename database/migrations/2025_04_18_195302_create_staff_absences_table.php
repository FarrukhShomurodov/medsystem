<?php

use App\Models\StaffAbsence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->enum('type', StaffAbsence::TYPE);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('reason', StaffAbsence::REASON)->default('other');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_absences');
    }
};
