<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('examination_map_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('dosage');
            $table->string('admission_period');

            $table->string('single_dose')->nullable();

            $table->enum('status', [
                'ordered',
                'sent_to_floor',
                'received_at_station',
                'completed',
                'returned',
                'cancelled',
            ])->default('ordered');

            $table->enum('method', [
                'iv_bolus_slow',
                'iv_drip',
                'oral',
                'intramuscular',
                'subcutaneous',
                'intradermal',
                'rectal',
                'nasal',
                'nasogastric',
                'sublingual',
                'auricular',
                'ophthalmic',
                'vaginal',
                'epidural',
                'transdermal',
                'intrathecal',
                'intraosseous',
                'endotracheal',
                'other',
            ]);
            $table->enum('admission_rules', [
                'anytime',
                'before_meal',
                'during_meal',
                'after_meal',
                'before_sleep',
                'empty_stomach',
            ]);
            $table->string('medication_notes')->nullable();

            $table->date('date_of_end');

            $table->decimal('price');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medications');
    }
};
