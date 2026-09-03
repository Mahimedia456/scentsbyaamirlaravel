<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('journal_posts')) {
            return;
        }

        $needsCategories = !Schema::hasColumn('journal_posts', 'wordpress_categories');
        $needsTags = !Schema::hasColumn('journal_posts', 'wordpress_tags');
        $needsModifiedAt = !Schema::hasColumn('journal_posts', 'wordpress_modified_at');

        if (!$needsCategories && !$needsTags && !$needsModifiedAt) {
            return;
        }

        Schema::table('journal_posts', function (Blueprint $table) use ($needsCategories, $needsTags, $needsModifiedAt) {
            if ($needsCategories) {
                $table->json('wordpress_categories')->nullable()->after('author_name');
            }

            if ($needsTags) {
                $table->json('wordpress_tags')->nullable()->after('wordpress_categories');
            }

            if ($needsModifiedAt) {
                $table->timestamp('wordpress_modified_at')->nullable()->after('published_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('journal_posts')) {
            return;
        }

        $columns = [];

        foreach (['wordpress_categories', 'wordpress_tags', 'wordpress_modified_at'] as $column) {
            if (Schema::hasColumn('journal_posts', $column)) {
                $columns[] = $column;
            }
        }

        if ($columns !== []) {
            Schema::table('journal_posts', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
