<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A department warehouse receives its medications as batches of the main warehouse. Keeping the
// batch number with the expiry date lets a recall or a complaint be traced to the department,
// and the receipt date is what the department lists show next to the batch.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_accountings', function (Blueprint $table): void {
            $table->string('batch_number')->nullable()->after('medication_id');
            $table->date('date_of_receipt')->nullable()->after('expiration_date');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_accountings', function (Blueprint $table): void {
            $table->dropColumn(['batch_number', 'date_of_receipt']);
        });
    }
};
