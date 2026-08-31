<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers','company')) $table->string('company',160)->nullable()->after('last_name');
            if (!Schema::hasColumn('customers','address')) $table->json('address')->nullable();
            if (!Schema::hasColumn('customers','notes')) $table->text('notes')->nullable();
            if (!Schema::hasColumn('customers','marketing_opt_in')) $table->boolean('marketing_opt_in')->default(false)->index();
            if (!Schema::hasColumn('customers','last_order_at')) $table->timestamp('last_order_at')->nullable()->index();
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders','billing_address')) $table->json('billing_address')->nullable();
            if (!Schema::hasColumn('orders','payment_method')) $table->string('payment_method',80)->nullable();
            if (!Schema::hasColumn('orders','shipping_method')) $table->string('shipping_method',120)->nullable();
            if (!Schema::hasColumn('orders','tracking_number')) $table->string('tracking_number',120)->nullable()->index();
            if (!Schema::hasColumn('orders','admin_notes')) $table->text('admin_notes')->nullable();
            if (!Schema::hasColumn('orders','fulfilled_at')) $table->timestamp('fulfilled_at')->nullable()->index();
        });
    }

    public function down(): void {}
};
