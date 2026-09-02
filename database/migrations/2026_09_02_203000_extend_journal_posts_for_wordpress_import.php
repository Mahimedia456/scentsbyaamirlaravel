<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('journal_posts')) {
            return;
        }

        Schema::table('journal_posts', function (Blueprint $table) {
            if (Schema::hasColumn('journal_posts', 'featured_image_path')) {
                $table->string('featured_image_path', 700)->nullable()->change();
            }
            if (! Schema::hasColumn('journal_posts', 'source')) {
                $table->string('source', 32)->default('manual')->after('status')->index();
            }
            if (! Schema::hasColumn('journal_posts', 'wordpress_id')) {
                $table->unsignedBigInteger('wordpress_id')->nullable()->after('source')->unique();
            }
            if (! Schema::hasColumn('journal_posts', 'wordpress_url')) {
                $table->string('wordpress_url', 700)->nullable()->after('wordpress_id');
            }
            if (! Schema::hasColumn('journal_posts', 'wordpress_modified_at')) {
                $table->timestamp('wordpress_modified_at')->nullable()->after('wordpress_url')->index();
            }
            if (! Schema::hasColumn('journal_posts', 'author_name')) {
                $table->string('author_name', 190)->nullable()->after('wordpress_modified_at');
            }
            if (! Schema::hasColumn('journal_posts', 'categories')) {
                $table->json('categories')->nullable()->after('author_name');
            }
            if (! Schema::hasColumn('journal_posts', 'tags')) {
                $table->json('tags')->nullable()->after('categories');
            }
            if (! Schema::hasColumn('journal_posts', 'canonical_url')) {
                $table->string('canonical_url', 700)->nullable()->after('meta_description');
            }
            if (! Schema::hasColumn('journal_posts', 'og_title')) {
                $table->string('og_title', 190)->nullable()->after('canonical_url');
            }
            if (! Schema::hasColumn('journal_posts', 'og_description')) {
                $table->text('og_description')->nullable()->after('og_title');
            }
            if (! Schema::hasColumn('journal_posts', 'og_image_path')) {
                $table->string('og_image_path', 700)->nullable()->after('og_description');
            }
            if (! Schema::hasColumn('journal_posts', 'imported_at')) {
                $table->timestamp('imported_at')->nullable()->after('published_at')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('journal_posts')) {
            return;
        }

        Schema::table('journal_posts', function (Blueprint $table) {
            foreach ([
                'imported_at', 'og_image_path', 'og_description', 'og_title', 'canonical_url',
                'tags', 'categories', 'author_name', 'wordpress_modified_at', 'wordpress_url',
                'wordpress_id', 'source',
            ] as $column) {
                if (Schema::hasColumn('journal_posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
