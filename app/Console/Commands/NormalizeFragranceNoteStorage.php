<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeFragranceNoteStorage extends Command
{
    protected $signature = 'storefront:normalize-fragrance-note-storage
        {--dry-run : Show changes without writing to the database}
        {--backup : Save current note fields to JSON before writing}';

    protected $description = 'Convert JSON-looking fragrance note arrays into clean comma-separated plain text.';

    private array $fields = ['top_notes', 'heart_notes', 'base_notes'];

    public function handle(): int
    {
        if (!Schema::hasTable('products')) {
            $this->error('Table "products" does not exist.');
            return self::FAILURE;
        }

        $fields = array_values(array_filter(
            $this->fields,
            fn (string $field) => Schema::hasColumn('products', $field)
        ));

        if ($fields === []) {
            $this->error('No structured fragrance-note columns exist.');
            return self::FAILURE;
        }

        $rows = DB::table('products')
            ->select(array_merge(['id', 'name', 'slug'], $fields))
            ->orderBy('id')
            ->get();

        $changes = [];

        foreach ($rows as $product) {
            $updates = [];

            foreach ($fields as $field) {
                $old = $product->{$field} ?? null;
                $new = $this->normalize($old);

                if (($old ?? null) !== $new) {
                    $updates[$field] = $new;
                }
            }

            if ($updates !== []) {
                $changes[] = [$product, $updates];
            }
        }

        $this->table(
            ['Product', 'Fields to normalize'],
            array_map(
                fn ($row) => [
                    $row[0]->name ?: $row[0]->slug ?: '#'.$row[0]->id,
                    implode(', ', array_keys($row[1])),
                ],
                $changes
            )
        );

        if ($changes === []) {
            $this->info('All fragrance-note fields are already stored as clean plain text.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info(count($changes).' product(s) would be normalized. No database changes made.');
            return self::SUCCESS;
        }

        if ($this->option('backup')) {
            $this->backup($changes, $fields);
        }

        DB::transaction(function () use ($changes) {
            foreach ($changes as [$product, $updates]) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update($updates);
            }
        });

        $this->info(count($changes).' product(s) normalized successfully.');
        return self::SUCCESS;
    }

    private function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $items = array_values(array_filter(array_map(
                fn ($item) => trim((string) $item),
                $decoded
            )));

            return $items !== [] ? implode(', ', $items) : null;
        }

        $text = preg_replace('/\s*,\s*/u', ', ', $text) ?? $text;
        $text = trim($text, " \t\n\r\0\x0B,[]\"");

        return $text !== '' ? $text : null;
    }

    private function backup(array $changes, array $fields): void
    {
        $backup = [];

        foreach ($changes as [$product]) {
            $row = [
                'id' => $product->id,
                'name' => $product->name ?? null,
                'slug' => $product->slug ?? null,
            ];

            foreach ($fields as $field) {
                $row[$field] = $product->{$field} ?? null;
            }

            $backup[] = $row;
        }

        $directory = storage_path('app/master-content-backups');

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $path = $directory.'/fragrance-notes-before-normalize-'.now()->format('Ymd-His').'.json';

        file_put_contents(
            $path,
            json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info('Backup written: '.$path);
    }
}
