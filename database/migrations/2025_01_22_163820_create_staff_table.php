<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->string('middle_name');
            $table->date('date_birthday');
            $table->enum('gender', ['female', 'male']);
            $table->string('citizenship');
            $table->string('avatar_url')->nullable();
            $table->string('sign_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
