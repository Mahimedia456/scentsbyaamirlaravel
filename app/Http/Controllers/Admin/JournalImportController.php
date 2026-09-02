<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WordPressJournalImporter;
use Illuminate\Http\Request;
use Throwable;

class JournalImportController extends Controller
{
    public function index()
    {
        return view('admin.journal-import.index', [
            'sourceUrl' => config('wordpress.url'),
        ]);
    }

    public function run(Request $request, WordPressJournalImporter $importer)
    {
        $data = $request->validate([
            'mode' => ['required', 'in:dry-run,import,update'],
            'images' => ['nullable', 'boolean'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        @set_time_limit(300);

        try {
            $stats = $importer->run(
                dryRun: $data['mode'] === 'dry-run',
                updateExisting: $data['mode'] === 'update',
                downloadImages: (bool) ($data['images'] ?? true),
                limit: isset($data['limit']) ? (int) $data['limit'] : null,
            );
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'WordPress import failed: '.$e->getMessage());
        }

        return back()->with('journal_import_stats', $stats)->with(
            'success',
            $data['mode'] === 'dry-run' ? 'WordPress journal preview complete.' : 'WordPress journal import complete.'
        );
    }
}
