<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'track_inventory')) {
                $table->boolean('track_inventory')->default(false)->after('stock');
            }

            if (!Schema::hasColumn('products', 'is_in_stock')) {
                $table->boolean('is_in_stock')->default(false)->index()->after('track_inventory');
            }

            if (!Schema::hasColumn('products', 'size_label')) {
                $table->string('size_label', 80)->nullable()->after('sku');
            }
        });

        // Preserve any products that already have a real tracked quantity.
        if (Schema::hasColumn('products', 'stock')) {
            DB::table('products')
                ->where('stock', '>', 0)
                ->update(['is_in_stock' => true]);
        }

        if (Schema::hasColumn('products', 'stock_quantity')) {
            DB::table('products')
                ->where('stock_quantity', '>', 0)
                ->update(['is_in_stock' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'size_label')) {
                $table->dropColumn('size_label');
            }

            if (Schema::hasColumn('products', 'is_in_stock')) {
                $table->dropColumn('is_in_stock');
            }
        });
    }
};
