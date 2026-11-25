<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_contacts');
    }
};
