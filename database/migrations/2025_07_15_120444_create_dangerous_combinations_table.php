<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dangerous_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('drug1_en')->nullable();
            $table->string('drug1_ru')->nullable();
            $table->string('drug2_en')->nullable();
            $table->string('drug2_ru')->nullable();
            $table->string('severity_en')->nullable();
            $table->string('severity_ru')->nullable();
            $table->string('mechanism_en')->nullable();
            $table->string('mechanism_ru')->nullable();
            $table->text('comment_en')->nullable();
            $table->text('comment_ru')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_interactions');
    }
};
