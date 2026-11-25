<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The 7-digit time part is historical: the name is kept so databases that already ran it stay in sync,
// and it still sorts before 2025_03_26_182648_create_surveys_table, which references this table.
return new class extends Migration
{
    public function up(): void
    {
        // Older databases created this table under 2025_06_21_142409_create_diagnoses_table.
        if (Schema::hasTable('diagnoses')) {
            return;
        }

        Schema::create('diagnoses', function (Blueprint $table): void {
            $table->id();
            $table->text('code');
            $table->text('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
