<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title',180);
            $table->string('slug',190)->unique();
            $table->string('template',80)->default('default');
            $table->longText('content')->nullable();
            $table->string('status',24)->default('draft')->index();
            $table->string('meta_title',190)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('journal_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title',190);
            $table->string('slug',200)->unique();
            $table->string('eyebrow',120)->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image_path')->nullable();
            $table->string('status',24)->default('draft')->index();
            $table->string('meta_title',190)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('navigations', function (Blueprint $table) {
            $table->id();
            $table->string('name',120);
            $table->string('key',120)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('navigation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->cascadeOnDelete();
            $table->string('label',140);
            $table->string('url',500)->nullable();
            $table->string('route_name',160)->nullable();
            $table->string('target',20)->default('_self');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
        Schema::dropIfExists('navigations');
        Schema::dropIfExists('journal_posts');
        Schema::dropIfExists('pages');
    }
};
