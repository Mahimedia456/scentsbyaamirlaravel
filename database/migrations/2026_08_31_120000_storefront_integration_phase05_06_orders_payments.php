<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_zone_id')) {
                $table->foreignId('shipping_zone_id')->nullable()->after('customer_id')->constrained('shipping_zones')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'payment_reference')) {
                $table->string('payment_reference', 190)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'payment_receipt_path')) {
                $table->string('payment_receipt_path')->nullable()->after('payment_reference');
            }
            if (!Schema::hasColumn('orders', 'payment_verification_status')) {
                $table->string('payment_verification_status', 32)->default('not_required')->index()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'payment_verified_at')) {
                $table->timestamp('payment_verified_at')->nullable()->after('payment_verification_status');
            }
            if (!Schema::hasColumn('orders', 'payment_verified_by')) {
                $table->foreignId('payment_verified_by')->nullable()->after('payment_verified_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'payment_rejection_reason')) {
                $table->text('payment_rejection_reason')->nullable()->after('payment_verified_by');
            }
        });
    }

    public function down(): void
    {
        // Additive production-safe migration. Columns are intentionally retained on rollback.
    }
};
