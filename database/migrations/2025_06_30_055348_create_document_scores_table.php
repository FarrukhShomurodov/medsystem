<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('document_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->integer('from_score');
            $table->integer('to_score')->nullable();
            $table->json('actions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_scores');
    }
};
