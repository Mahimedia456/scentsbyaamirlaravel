<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders','coupon_code')) $table->string('coupon_code',80)->nullable()->index();
            if (!Schema::hasColumn('orders','gift_wrap')) $table->boolean('gift_wrap')->default(false);
            if (!Schema::hasColumn('orders','gift_wrap_total')) $table->decimal('gift_wrap_total',12,2)->default(0);
            if (!Schema::hasColumn('orders','gift_message')) $table->text('gift_message')->nullable();
            if (!Schema::hasColumn('orders','gift_sender_name')) $table->string('gift_sender_name',160)->nullable();
            if (!Schema::hasColumn('orders','checkout_token')) $table->string('checkout_token',100)->nullable()->unique();
            if (!Schema::hasColumn('orders','inventory_restocked_at')) $table->timestamp('inventory_restocked_at')->nullable();
        });

        if (!Schema::hasTable('customer_notifications')) {
            Schema::create('customer_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type',80)->index();
                $table->string('title',190);
                $table->text('message');
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable()->index();
                $table->timestamps();
                $table->index(['customer_id','created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notifications');
        // Order columns intentionally retained on rollback to avoid losing commerce history.
    }
};
