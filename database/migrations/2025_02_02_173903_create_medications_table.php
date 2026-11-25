<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->enum('release_form', [
                'Таблетка',
                'Ампула',
                'Жидкость',
                'Сироп',
                'Капсула',
                'Порошок',
                'Гранулы',
                'Экстракт',
                'Пастилка',
                'Спрей',
                'Раствор',
                'Свечи ректальные',
                'Мазь',
                'Гель',
                'Пакетик',
                'Крем',
                'Капли',
                'Масло',
                'Глазные капли',
                'Свечи вагинальные',
                'Ушные капли',
                'Суспензия',
                'Пластырь',
                'Настойка',
                'Паста',
                'Флакон',
                'Эластичный бинт',
                'Шприц',
                'Вата',
                'Саше',
                'Рулон',
                'Бинт',
                'Система',
                'Бальзам',
            ]);
            $table->string('dosage');
            $table->foreignId('manufacturer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('medication_groups')->cascadeOnDelete();
            $table->string('registration_number');
            $table->date('registration_date');

            $table->integer('min_deduction');
            $table->integer('min_quantity');
            $table->integer('notify_before_days');
            $table->integer('notify_min_qty');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
