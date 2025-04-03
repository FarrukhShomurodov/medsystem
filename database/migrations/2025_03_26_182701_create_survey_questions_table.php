<?php

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Survey::class);
            $table->string('question');
            $table->enum('type', [SurveyQuestion::TYPE]);
            $table->json('options')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
