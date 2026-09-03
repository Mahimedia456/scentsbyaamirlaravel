<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('journal_posts')) {
            return;
        }

        Schema::table('journal_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_posts', 'wordpress_id')) {
                $table->unsignedBigInteger('wordpress_id')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('journal_posts', 'source_url')) {
                $table->string('source_url', 500)->nullable()->after('featured_image_path');
            }
            if (!Schema::hasColumn('journal_posts', 'author_name')) {
                $table->string('author_name', 190)->nullable()->after('source_url');
            }
            if (!Schema::hasColumn('journal_posts', 'wordpress_categories')) {
                $table->json('wordpress_categories')->nullable()->after('author_name');
            }
            if (!Schema::hasColumn('journal_posts', 'wordpress_tags')) {
                $table->json('wordpress_tags')->nullable()->after('wordpress_categories');
            }
            if (!Schema::hasColumn('journal_posts', 'wordpress_modified_at')) {
                $table->timestamp('wordpress_modified_at')->nullable()->after('published_at');
            }
            if (!Schema::hasColumn('journal_posts', 'imported_at')) {
                $table->timestamp('imported_at')->nullable()->after('wordpress_modified_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('journal_posts')) {
            return;
        }

        Schema::table('journal_posts', function (Blueprint $table) {
            foreach (['wordpress_id', 'source_url', 'author_name', 'wordpress_categories', 'wordpress_tags', 'wordpress_modified_at', 'imported_at'] as $column) {
                if (Schema::hasColumn('journal_posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
