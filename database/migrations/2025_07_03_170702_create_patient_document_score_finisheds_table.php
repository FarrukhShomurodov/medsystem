<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_document_score_finisheds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_score_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_action_id')->constrained('document_score_actions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_document_score_finisheds');
    }
};
