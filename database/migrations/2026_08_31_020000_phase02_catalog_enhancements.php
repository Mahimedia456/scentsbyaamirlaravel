<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories','description')) $table->text('description')->nullable();
            if (!Schema::hasColumn('categories','is_active')) $table->boolean('is_active')->default(true)->index();
            if (!Schema::hasColumn('categories','sort_order')) $table->unsignedInteger('sort_order')->default(0)->index();
        });
        Schema::table('collections', function (Blueprint $table) {
            if (!Schema::hasColumn('collections','description')) $table->text('description')->nullable();
            if (!Schema::hasColumn('collections','is_active')) $table->boolean('is_active')->default(true)->index();
            if (!Schema::hasColumn('collections','sort_order')) $table->unsignedInteger('sort_order')->default(0)->index();
        });
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products','category_id')) $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            if (!Schema::hasColumn('products','subtitle')) $table->string('subtitle',200)->nullable();
            if (!Schema::hasColumn('products','story')) $table->text('story')->nullable();
            if (!Schema::hasColumn('products','notes')) $table->text('notes')->nullable();
            if (!Schema::hasColumn('products','wear')) $table->text('wear')->nullable();
            if (!Schema::hasColumn('products','status')) $table->string('status',24)->default('draft')->index();
            if (!Schema::hasColumn('products','is_featured')) $table->boolean('is_featured')->default(false)->index();
            if (!Schema::hasColumn('products','base_price')) $table->decimal('base_price',12,2)->default(0);
            if (!Schema::hasColumn('products','compare_at_price')) $table->decimal('compare_at_price',12,2)->nullable();
            if (!Schema::hasColumn('products','stock')) $table->unsignedInteger('stock')->default(0);
            if (!Schema::hasColumn('products','sku')) $table->string('sku',100)->nullable()->unique();
            if (!Schema::hasColumn('products','meta_title')) $table->string('meta_title',180)->nullable();
            if (!Schema::hasColumn('products','meta_description')) $table->string('meta_description',500)->nullable();
        });
    }
    public function down(): void {}
};
