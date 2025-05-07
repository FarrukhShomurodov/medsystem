<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            // Основные данные
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('citizenship');
            $table->string('passport_series');
            $table->date('passport_validity_period');

            // Контактная информация
            $table->string('phone');
            $table->string('additional_phone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('residential_address_street');
            $table->string('residential_address_house');
            $table->string('residential_address_apartment');
            $table->integer('residential_postcode')->nullable();

            // Адрес регистрации
            $table->foreignId('registered_region_id')->nullable()->constrained('regions')->cascadeOnDelete();
            $table->foreignId('registered_city_id')->nullable()->constrained('cities')->cascadeOnDelete();
            $table->string('registered_address_street')->nullable();
            $table->string('registered_address_house')->nullable();
            $table->string('registered_address_apartment')->nullable();
            $table->integer('registered_postcode')->nullable();

            // Страховка
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_type')->nullable();
            $table->date('insurance_expiry_date')->nullable();

            // Данные экстренных контакто
            $table->string('emergency_contact_full_name');
            $table->string('emergency_contact_relation');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_address')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
