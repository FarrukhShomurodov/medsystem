<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Stock rows only hold the current quantity. The ledger keeps every change of it: who moved how
// many units of which item out of or into which row, and for which document (a supply request,
// a transfer, a prescription). Written-off batches leave their trace here, and a returned
// prescription goes back exactly into the rows it was dispensed from.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->morphs('stock');
            $table->morphs('item');
            // Signed: units into the row are positive, units out of it negative.
            $table->integer('quantity');
            $table->enum('reason', ['receipt', 'write_off', 'supply_request', 'transfer', 'dispense', 'return']);
            $table->nullableMorphs('source');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
