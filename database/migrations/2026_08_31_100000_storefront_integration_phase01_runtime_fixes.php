<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'stock')) {
                    $table->unsignedInteger('stock')->default(0)->after('compare_at_price');
                }
                if (!Schema::hasColumn('product_variants', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('is_active');
                }
            });

            if (Schema::hasColumn('product_variants', 'stock_quantity') && Schema::hasColumn('product_variants', 'stock')) {
                DB::table('product_variants')
                    ->where('stock', 0)
                    ->where('stock_quantity', '>', 0)
                    ->update(['stock' => DB::raw('stock_quantity')]);
            }
        }
    }

    public function down(): void
    {
        // Compatibility migration: deliberately non-destructive on rollback.
    }
};
