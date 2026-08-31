<?php
namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderInventoryService
{
    public function restockCancelledOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->inventory_restocked_at) return;
            $locked->load('items');
            foreach ($locked->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::query()->whereKey($item->product_variant_id)->lockForUpdate()->first();
                    if (!$variant) continue;
                    $after = (int)$variant->stock + (int)$item->quantity;
                    $variant->update(['stock'=>$after]);
                    InventoryAdjustment::create(['product_id'=>$item->product_id,'product_variant_id'=>$variant->id,'user_id'=>auth()->id(),'quantity_change'=>(int)$item->quantity,'quantity_after'=>$after,'reason'=>'order_cancelled','reference'=>$locked->order_number,'note'=>'Inventory restored after order cancellation.']);
                } else {
                    $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                    if (!$product) continue;
                    $after = (int)$product->stock + (int)$item->quantity;
                    $product->update(['stock'=>$after]);
                    InventoryAdjustment::create(['product_id'=>$product->id,'product_variant_id'=>null,'user_id'=>auth()->id(),'quantity_change'=>(int)$item->quantity,'quantity_after'=>$after,'reason'=>'order_cancelled','reference'=>$locked->order_number,'note'=>'Inventory restored after order cancellation.']);
                }
            }
            $locked->update(['inventory_restocked_at'=>now()]);
        }, 3);
    }
}
