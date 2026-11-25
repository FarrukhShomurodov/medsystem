<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id');
            $table->string('question');
            $table->enum('type', ['choice', 'boolean', 'text']);
            $table->json('options')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_questions');
    }
};
