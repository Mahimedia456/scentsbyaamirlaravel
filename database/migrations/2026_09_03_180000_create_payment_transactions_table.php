<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('provider', 32)->default('ubl')->index();
                $table->uuid('public_token')->unique();
                $table->unsignedSmallInteger('attempt')->default(1);
                $table->string('environment', 24)->default('sandbox');
                $table->string('status', 32)->default('created')->index();
                $table->string('gateway_order_id', 32)->nullable()->index();
                $table->string('gateway_transaction_id', 100)->nullable()->index();
                $table->string('gateway_unique_id', 120)->nullable();
                $table->string('approval_code', 120)->nullable();
                $table->string('response_code', 32)->nullable()->index();
                $table->string('response_class', 32)->nullable();
                $table->text('response_description')->nullable();
                $table->string('card_brand', 64)->nullable();
                $table->string('masked_card_number', 64)->nullable();
                $table->decimal('amount', 14, 2);
                $table->string('currency', 8)->default('PKR');
                $table->text('payment_portal_url')->nullable();
                $table->json('request_payload')->nullable();
                $table->json('registration_response')->nullable();
                $table->json('finalization_response')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamp('registered_at')->nullable();
                $table->timestamp('finalized_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();
                $table->unique(['order_id', 'provider', 'attempt']);
            });
        }

        if (Schema::hasTable('payment_methods')) {
            DB::table('payment_methods')->updateOrInsert(
                ['code' => 'ubl_card'],
                [
                    'name' => 'Debit / Credit Card (UBL)',
                    'enabled' => false,
                    'test_mode' => true,
                    'config' => json_encode([
                        'customer_note' => 'Secure Visa / Mastercard payment via UBL hosted checkout.',
                    ]),
                    'sort_order' => 15,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
