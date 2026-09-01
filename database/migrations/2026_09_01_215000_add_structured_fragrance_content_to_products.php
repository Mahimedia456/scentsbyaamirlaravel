<?php

use App\Services\ProductContentParser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'top_notes')) {
                $table->text('top_notes')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('products', 'heart_notes')) {
                $table->text('heart_notes')->nullable()->after('top_notes');
            }

            if (!Schema::hasColumn('products', 'base_notes')) {
                $table->text('base_notes')->nullable()->after('heart_notes');
            }
        });

        $parser = new ProductContentParser();

        DB::table('products')
            ->select(['id', 'description', 'notes', 'top_notes', 'heart_notes', 'base_notes'])
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($parser) {
                foreach ($products as $product) {
                    $parsed = $parser->parse($product->description, $product->notes);

                    $updates = [];

                    if (blank($product->top_notes) && filled($parsed['top_notes'])) {
                        $updates['top_notes'] = $parsed['top_notes'];
                    }

                    if (blank($product->heart_notes) && filled($parsed['heart_notes'])) {
                        $updates['heart_notes'] = $parsed['heart_notes'];
                    }

                    if (blank($product->base_notes) && filled($parsed['base_notes'])) {
                        $updates['base_notes'] = $parsed['base_notes'];
                    }

                    if (filled($parsed['description'])) {
                        $updates['description'] = $parsed['description'];
                    }

                    if (filled($parsed['notes_summary'])) {
                        $updates['notes'] = $parsed['notes_summary'];
                    }

                    if ($updates !== []) {
                        DB::table('products')
                            ->where('id', $product->id)
                            ->update(array_merge($updates, ['updated_at' => now()]));
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['base_notes', 'heart_notes', 'top_notes'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
