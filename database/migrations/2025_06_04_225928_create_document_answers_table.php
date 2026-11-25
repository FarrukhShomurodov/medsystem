<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('document_questions')->cascadeOnDelete();
            $table->text('answer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_answers');
    }
};
