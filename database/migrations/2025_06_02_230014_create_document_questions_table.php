<?php

use App\Models\Document;
use App\Models\DocumentQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Document::class);
            $table->string('question');
            $table->enum('type', [DocumentQuestion::TYPE]);
            $table->json('options')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_questions');
    }
};
