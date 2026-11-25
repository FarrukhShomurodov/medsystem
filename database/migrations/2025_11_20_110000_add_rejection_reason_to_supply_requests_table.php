<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The main warehouse may reject a department's supply request; the department sees why.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_requests', function (Blueprint $table): void {
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('supply_requests', function (Blueprint $table): void {
            $table->dropColumn('rejection_reason');
        });
    }
};
