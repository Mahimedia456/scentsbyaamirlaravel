<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Repair installations where the old platform migration was recorded as
        // completed but these two tables were absent or were manually removed.
        if (!Schema::hasTable('woocommerce_import_runs')) {
            Schema::create('woocommerce_import_runs', function (Blueprint $table) {
                $table->id();
                $table->string('status')->default('pending');
                $table->string('source_url')->nullable();
                $table->json('options')->nullable();
                $table->json('summary')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('woocommerce_import_maps')) {
            Schema::create('woocommerce_import_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('woocommerce_import_runs')->cascadeOnDelete();
                $table->string('entity_type');
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('local_id')->nullable();
                $table->string('status')->default('imported');
                $table->text('message')->nullable();
                $table->timestamps();
                $table->unique(['run_id', 'entity_type', 'source_id']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. These tables may contain the one-time
        // migration audit/mapping required to safely re-run the importer.
    }
};
