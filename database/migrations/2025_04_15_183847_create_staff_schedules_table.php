<?php

use App\Models\StaffSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->enum('day_start', StaffSchedule::DAY_START);
            $table->enum('day_end', StaffSchedule::DAY_END);
            $table->time('start_time');
            $table->time('end_time');
            $table->time('reception_start');
            $table->time('reception_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');
    }
};
