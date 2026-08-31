<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('key', 190)->nullable()->unique();
                $table->string('type', 40)->default('info')->index();
                $table->string('title', 190);
                $table->text('message')->nullable();
                $table->string('action_url', 500)->nullable();
                $table->string('action_label', 80)->nullable();
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable()->index();
                $table->timestamp('dismissed_at')->nullable()->index();
                $table->timestamp('resolved_at')->nullable()->index();
                $table->timestamps();

                $table->index(['type', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
