<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_storage_handlings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('temperature');
            $table->string('humidity');
            $table->string('light_protected');
            $table->boolean('is_dangerous');
            $table->string('refrigeration');
            $table->string('freezing');
            $table->string('flammable');
            $table->string('shelf_location');
            $table->string('special_zone');
            $table->string('label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_storage_handlings');
    }
};
