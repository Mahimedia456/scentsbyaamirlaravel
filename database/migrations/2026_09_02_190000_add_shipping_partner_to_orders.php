<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders') || Schema::hasColumn('orders', 'shipping_partner')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_partner', 60)
                ->nullable()
                ->after('shipping_method')
                ->index();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'shipping_partner')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('shipping_partner');
            });
        }
    }
};
