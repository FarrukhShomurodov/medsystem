<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyze_to_type_assignment', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('type_id')->constrained('analyze_types')->cascadeOnDelete();
            $table->foreignId('analyze_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyze_to_type_assignment');
    }
};
