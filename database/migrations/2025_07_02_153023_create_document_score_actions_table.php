<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_score_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_score_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_score_actions');
    }
};
