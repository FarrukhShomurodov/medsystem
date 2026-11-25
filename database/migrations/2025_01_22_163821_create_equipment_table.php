<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('equipment_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manufacturer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('serial_number');
            $table->date('date_purchase');
            $table->date('calibration_date');
            $table->string('repair');
            $table->date('term_date');
            $table->integer('term_notify_days');
            $table->integer('upcoming_service_at');
            $table->string('location');
            $table->decimal('price');
            $table->date('last_inspection')->nullable();
            $table->enum('status', ['работает', 'сломан'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
