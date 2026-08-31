<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Phase 02 compatibility: variants use these admin-facing columns.
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants','stock')) $table->unsignedInteger('stock')->default(0)->after('stock_quantity');
            if (!Schema::hasColumn('product_variants','sort_order')) $table->unsignedInteger('sort_order')->default(0)->index();
        });

        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity_change');
            $table->unsignedInteger('quantity_after')->default(0);
            $table->string('reason',80)->default('manual');
            $table->string('reference',160)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['product_id','created_at']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code',80)->unique();
            $table->string('name',160)->nullable();
            $table->enum('type',['percentage','fixed'])->default('percentage');
            $table->decimal('value',12,2);
            $table->decimal('minimum_order',12,2)->nullable();
            $table->decimal('maximum_discount',12,2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->json('product_ids')->nullable();
            $table->json('collection_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount',12,2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('inventory_adjustments');
    }
};
