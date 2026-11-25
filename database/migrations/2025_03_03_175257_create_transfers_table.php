<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_pharmacy_id')->constrained('pharmacies');
            $table->date('transfer_date');
            $table->enum('status', ['pending', 'completed', 'canceled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
