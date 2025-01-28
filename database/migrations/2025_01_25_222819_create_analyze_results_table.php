<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('analyze_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analyze_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('analyze_types')->cascadeOnDelete();
            $table->string('unit');
            $table->integer('value');
            $table->integer('reference_range');
            $table->longText('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyze_results');
    }
};
