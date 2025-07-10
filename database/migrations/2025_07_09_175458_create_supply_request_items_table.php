<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supply_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('supply_requests')->cascadeOnDelete();
            $table->morphs('supplyable');
            $table->integer('requested_quantity');
            $table->integer('approved_quantity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_request_items');
    }
};
