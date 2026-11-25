<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->enum('color', [
                '#F4EAD5',
                '#DFF6FF',
                '#E0FFE0',
                '#FFF5CC',
                '#FFEBE0',
                '#E6E6FA',
                '#F0FFF0',
                '#FAFAD2',
                '#FFE4E1',
                '#F5FFFA',
                '#FFD700',
                '#FF69B4',
                '#00CED1',
                '#FFA07A',
                '#98FB98',
                '#FFB6C1',
                '#FF8C00',
                '#7FFFD4',
                '#FF6347',
                '#87CEFA',
            ]);
            $table->foreignId('survey_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diagnosis_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
