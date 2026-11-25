<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// `stock` duplicated the remaining quantity of a main warehouse batch, but nothing has kept it
// since StockLedger moves stock (it changes `quantity` only), so its values are stale.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_warehouse_medications', function (Blueprint $table): void {
            $table->dropColumn('stock');
        });
    }

    public function down(): void
    {
        Schema::table('main_warehouse_medications', function (Blueprint $table): void {
            $table->integer('stock')->default(0);
        });

        // The column meant "what is left of the batch", which is what `quantity` holds.
        DB::table('main_warehouse_medications')->update(['stock' => DB::raw('quantity')]);
    }
};
