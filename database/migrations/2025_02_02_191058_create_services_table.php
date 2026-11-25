<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('code')->nullable();
            $table->decimal('price');
            $table->integer('discount')->nullable();
            $table->string('currency')->nullable();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_group_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_appointment')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
