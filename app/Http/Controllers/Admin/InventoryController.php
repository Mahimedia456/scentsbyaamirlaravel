<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['variants'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->q);
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('mode'), function ($query) use ($request) {
                $request->mode === 'tracked'
                    ? $query->where('track_inventory', true)
                    : $query->where('track_inventory', false);
            })
            ->when($request->filled('state'), function ($query) use ($request) {
                match ($request->state) {
                    'low' => $query->where('track_inventory', true)->whereBetween('stock', [1, 5]),
                    'out' => $query->where(function ($q) {
                        $q->where(fn ($tracked) => $tracked->where('track_inventory', true)->where('stock', '<=', 0))
                            ->orWhere(fn ($simple) => $simple->where('track_inventory', false)->where('is_in_stock', false));
                    }),
                    'in' => $query->where(function ($q) {
                        $q->where(fn ($tracked) => $tracked->where('track_inventory', true)->where('stock', '>', 0))
                            ->orWhere(fn ($simple) => $simple->where('track_inventory', false)->where('is_in_stock', true));
                    }),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $adjustments = InventoryAdjustment::query()
            ->with(['product', 'variant', 'user'])
            ->when($request->filled('reason'), fn ($q) => $q->where('reason', $request->reason))
            ->when($request->filled('movement_product_id'), fn ($q) => $q->where('product_id', $request->movement_product_id))
            ->latest()
            ->limit(60)
            ->get();

        $summary = [
            'tracked' => Product::where('track_inventory', true)->count(),
            'untracked' => Product::where('track_inventory', false)->count(),
            'low' => Product::where('track_inventory', true)->whereBetween('stock', [1, 5])->count(),
            'out' => Product::where(function ($q) {
                $q->where(fn ($tracked) => $tracked->where('track_inventory', true)->where('stock', '<=', 0))
                    ->orWhere(fn ($simple) => $simple->where('track_inventory', false)->where('is_in_stock', false));
            })->count(),
        ];

        $adjustableProducts = Product::query()->orderBy('name')->get(['id','name','sku']);

        return view('admin.inventory.index', compact('products', 'adjustments', 'summary', 'adjustableProducts'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required','exists:products,id'],
            'product_variant_id' => ['nullable','exists:product_variants,id'],
            'quantity_change' => ['required','integer','not_in:0'],
            'reason' => ['required', Rule::in(['manual','received','damage','correction','return','reserved','cycle_count'])],
            'reference' => ['nullable','string','max:160'],
            'note' => ['nullable','string','max:1000'],
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            $variant = !empty($data['product_variant_id'])
                ? ProductVariant::lockForUpdate()
                    ->where('product_id', $product->id)
                    ->findOrFail($data['product_variant_id'])
                : null;

            $current = $variant
                ? max((int) $variant->stock, (int) ($variant->stock_quantity ?? 0))
                : max((int) $product->stock, (int) ($product->stock_quantity ?? 0));

            $after = max(0, $current + (int) $data['quantity_change']);

            if ($variant) {
                $variant->update([
                    'stock' => $after,
                    'stock_quantity' => $after,
                ]);
            } else {
                $product->update([
                    'stock' => $after,
                    'stock_quantity' => $after,
                    'track_inventory' => true,
                    'is_in_stock' => $after > 0,
                ]);
            }

            InventoryAdjustment::create($data + [
                'quantity_after' => $after,
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Inventory adjusted and movement logged.');
    }

    public function availability(Request $request, Product $product)
    {
        $data = $request->validate([
            'is_in_stock' => ['required', 'boolean'],
        ]);

        $product->update([
            'is_in_stock' => (bool) $data['is_in_stock'],
            'track_inventory' => false,
        ]);

        return back()->with('success', 'Simple-product availability updated.');
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = InventoryAdjustment::query()
            ->with(['product', 'variant', 'user'])
            ->when($request->filled('reason'), fn ($q) => $q->where('reason', $request->reason))
            ->latest()
            ->limit(5000)
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Product','Variant','SKU','Change','After','Reason','Reference','Admin','Note']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    optional($row->created_at)->toDateTimeString(),
                    optional($row->product)->name,
                    optional($row->variant)->name,
                    optional($row->variant)->sku ?: optional($row->product)->sku,
                    $row->quantity_change,
                    $row->quantity_after,
                    $row->reason,
                    $row->reference,
                    optional($row->user)->name,
                    $row->note,
                ]);
            }

            fclose($out);
        }, 'inventory-movements-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
