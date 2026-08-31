<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WooCommerceImportRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Services\WooCommerceImporter;

class WooCommerceImportController extends Controller
{
    public function index()
    {
        $setupRequired = !Schema::hasTable('woocommerce_import_runs');
        $runs = $setupRequired ? collect() : WooCommerceImportRun::latest()->limit(20)->get();
        return view('admin.woocommerce.index', compact('runs', 'setupRequired'));
    }

    public function test(Request $request)
    {
        $data = $request->validate([
            'source_url' => 'required|url',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
        ]);

        try {
            $url = rtrim($data['source_url'], '/') . '/wp-json/wc/v3/system_status';
            $response = Http::withBasicAuth($data['consumer_key'], $data['consumer_secret'])->timeout(20)->get($url);

            return back()->with(
                $response->successful() ? 'success' : 'error',
                $response->successful() ? 'WooCommerce connection successful.' : 'Connection failed: HTTP ' . $response->status()
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'WooCommerce connection could not be completed. Check the URL, API keys and server connectivity.');
        }
    }

    public function store(Request $request, WooCommerceImporter $importer)
    {
        if (!Schema::hasTable('woocommerce_import_runs')) {
            return back()->with('error', 'WooCommerce migration tables are not installed yet. Run php artisan migrate first.');
        }

        $data = $request->validate([
            'source_url' => 'required|url',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
        ]);

        $run = WooCommerceImportRun::create([
            'status' => 'queued',
            'source_url' => $data['source_url'],
            'options' => [
                'products' => $request->boolean('products'),
                'customers' => $request->boolean('customers'),
                'orders' => $request->boolean('orders'),
                'categories' => $request->boolean('categories'),
                'media' => $request->boolean('media'),
            ],
            'summary' => ['note' => 'Migration run created. Use the queued importer command/job in production for large datasets.'],
        ]);

        if ($request->boolean('run_now')) {
            try {
                $importer->run($run, $data['consumer_key'], $data['consumer_secret']);
                return back()->with('success', 'WooCommerce import #' . $run->id . ' completed. Review the migration report below.');
            } catch (\Throwable $e) {
                report($e);
                return back()->with('error', 'Import #' . $run->id . ' stopped: ' . $e->getMessage());
            }
        }
        return back()->with('success', 'Import run #' . $run->id . ' queued. Run php artisan woocommerce:import ' . $run->id . ' with API credentials for large production datasets.');
    }
}
