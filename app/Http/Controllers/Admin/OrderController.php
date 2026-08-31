<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\Product;
use App\Services\CustomerNotificationService;
use App\Services\OrderInventoryService;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('customer')->withCount('items')
            ->when($request->filled('q'), fn($q) => $q->where(fn($x) => $x
                ->where('order_number','like','%'.$request->q.'%')
                ->orWhere('customer_name','like','%'.$request->q.'%')
                ->orWhere('customer_email','like','%'.$request->q.'%')
                ->orWhere('tracking_number','like','%'.$request->q.'%')))
            ->when($request->filled('status'), fn($q) => $q->where('status',$request->status))
            ->when($request->filled('payment_status'), fn($q) => $q->where('payment_status',$request->payment_status))
            ->when($request->filled('payment_method'), fn($q) => $q->where('payment_method',$request->payment_method))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('placed_at','>=',$request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('placed_at','<=',$request->date_to))
            ->latest('placed_at')->latest()
            ->paginate(25)->withQueryString();

        $summary = [
            'all' => Order::count(),
            'open' => Order::whereIn('status',['pending','confirmed','processing'])->count(),
            'shipped' => Order::where('status','shipped')->count(),
            'delivered' => Order::where('status','delivered')->count(),
            'payment_pending' => Order::where('payment_status','pending')->count(),
        ];

        return view('admin.orders.index',compact('orders','summary'));
    }

    public function create()
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','email','phone']);

        $products = Product::query()
            ->where('status','active')
            ->orderBy('name')
            ->get(['id','name','sku','base_price','size_label','track_inventory','stock','stock_quantity','is_in_stock']);

        return view('admin.orders.create', compact('customers','products'));
    }

    public function store(
        Request $request,
        CustomerNotificationService $notifications,
        TransactionalMailService $mail
    ) {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn ($row) => !empty($row['product_id']))
                ->values()
                ->all(),
        ]);

        $data = $request->validate([
            'customer_id' => ['required','exists:customers,id'],
            'status' => ['required',Rule::in(['pending','confirmed','processing'])],
            'payment_status' => ['required',Rule::in(['pending','paid'])],
            'payment_method' => ['required',Rule::in(['cod','bank_transfer','manual'])],
            'shipping_method' => ['nullable','string','max:120'],
            'shipping_total' => ['nullable','numeric','min:0'],
            'discount_total' => ['nullable','numeric','min:0'],
            'notes' => ['nullable','string','max:1000'],
            'admin_notes' => ['nullable','string','max:3000'],
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required','exists:products,id'],
            'items.*.qty' => ['required','integer','min:1','max:99'],
        ]);

        $customer = Customer::with('addresses')->findOrFail($data['customer_id']);

        $order = DB::transaction(function () use ($data, $customer) {
            $prepared = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $row) {
                $qty = (int) $row['qty'];
                $product = Product::query()->lockForUpdate()->findOrFail((int) $row['product_id']);

                if ($product->status !== 'active') {
                    abort(422, "{$product->name} is not active.");
                }

                $tracked = (bool) ($product->track_inventory ?? false);
                $stock = max((int)($product->stock ?? 0), (int)($product->stock_quantity ?? 0));

                if ($tracked && $stock < $qty) {
                    abort(422, "{$product->name} only has {$stock} item(s) available.");
                }

                if (!$tracked && !(bool)($product->is_in_stock ?? false)) {
                    abort(422, "{$product->name} is currently out of stock.");
                }

                $price = (float)($product->base_price ?? 0);
                $lineTotal = round($price * $qty, 2);
                $subtotal += $lineTotal;
                $prepared[] = compact('product','qty','price','lineTotal','tracked','stock');
            }

            $discount = min((float)($data['discount_total'] ?? 0), $subtotal);
            $shipping = (float)($data['shipping_total'] ?? 0);
            $address = $customer->addresses->firstWhere('is_default', true) ?: $customer->addresses->first();
            $snapshot = $address ? [
                'first_name'=>$address->first_name,
                'last_name'=>$address->last_name,
                'phone'=>$address->phone ?: $customer->phone,
                'address_line_1'=>$address->address_line_1,
                'address_line_2'=>$address->address_line_2,
                'city'=>$address->city,
                'region'=>$address->region,
                'postal_code'=>$address->postal_code,
                'country_code'=>$address->country_code ?: 'PK',
            ] : [
                'first_name'=>$customer->first_name,
                'last_name'=>$customer->last_name,
                'phone'=>$customer->phone,
            ];

            $order = Order::create([
                'order_number' => $this->orderNumber('ADM'),
                'customer_id' => $customer->id,
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'payment_verification_status' => 'not_required',
                'currency' => 'PKR',
                'subtotal' => round($subtotal,2),
                'discount_total' => round($discount,2),
                'shipping_total' => round($shipping,2),
                'gift_wrap_total' => 0,
                'grand_total' => round(max(0,$subtotal-$discount)+$shipping,2),
                'customer_name' => $customer->full_name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'shipping_address' => $snapshot,
                'billing_address' => $snapshot,
                'payment_method' => $data['payment_method'],
                'shipping_method' => $data['shipping_method'] ?: 'Admin order',
                'notes' => $data['notes'] ?? null,
                'admin_notes' => $data['admin_notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($prepared as $line) {
                $product = $line['product'];
                $order->items()->create([
                    'product_id'=>$product->id,
                    'product_variant_id'=>null,
                    'product_name'=>$product->name . ($product->size_label ? ' — '.$product->size_label : ''),
                    'sku'=>$product->sku,
                    'quantity'=>$line['qty'],
                    'unit_price'=>$line['price'],
                    'line_total'=>$line['lineTotal'],
                ]);

                if ($line['tracked']) {
                    $after = $line['stock'] - $line['qty'];
                    $product->update([
                        'stock'=>$after,
                        'stock_quantity'=>$after,
                        'is_in_stock'=>$after > 0,
                    ]);

                    if (Schema::hasTable('inventory_adjustments')) {
                        InventoryAdjustment::create([
                            'product_id'=>$product->id,
                            'product_variant_id'=>null,
                            'user_id'=>auth()->id(),
                            'quantity_change'=>-$line['qty'],
                            'quantity_after'=>$after,
                            'reason'=>'order',
                            'reference'=>$order->order_number,
                            'note'=>'Admin-created order.',
                        ]);
                    }
                }
            }

            return $order->load('items');
        });

        $notifications->orderPlaced($order);
        $mail->order($order,'placed');
        $this->audit($order,'admin_order_created');

        return redirect()->route('admin.orders.show',$order)->with('success','Order created. Invoice is ready.');
    }

    public function bulk(Request $request, CustomerNotificationService $notifications, OrderInventoryService $inventory, TransactionalMailService $mail)
    {
        $data = $request->validate([
            'ids' => ['required','array','min:1'],
            'ids.*' => ['integer','exists:orders,id'],
            'action' => ['required', Rule::in(['confirm','processing','shipped','delivered','cancelled'])],
        ]);

        $target = $data['action'] === 'confirm' ? 'confirmed' : $data['action'];

        foreach (Order::query()->whereIn('id',$data['ids'])->get() as $order) {
            $old = $order->status;
            if ($old === $target) continue;

            $changes = ['status'=>$target];
            if (in_array($target,['shipped','delivered'],true) && !$order->fulfilled_at) {
                $changes['fulfilled_at']=now();
            }

            $order->update($changes);

            if ($target === 'cancelled') {
                $inventory->restockCancelledOrder($order);
            }

            $fresh=$order->fresh();
            $notifications->orderUpdated($fresh,$old);
            $mail->order($fresh,$target);
        }

        return back()->with('success',count($data['ids']).' order(s) updated and customer notifications processed.');
    }

    public function show(Order $order)
    {
        $order->load(['customer','items.product','items.variant']);
        return view('admin.orders.show',compact('order'));
    }

    public function invoice(Order $order)
    {
        $order->load(['customer','items.product','items.variant']);

        return view('admin.orders.invoice', compact('order'));
    }

    public function update(Request $request, Order $order, CustomerNotificationService $notifications, OrderInventoryService $inventory, TransactionalMailService $mail)
    {
        $oldStatus=$order->status;
        $oldPaymentStatus=$order->payment_status;

        $data=$request->validate([
            'status'=>['required',Rule::in(['pending','confirmed','processing','shipped','delivered','cancelled','refunded'])],
            'payment_status'=>['required',Rule::in(['pending','paid','failed','refunded'])],
            'payment_method'=>['nullable','string','max:80'],
            'shipping_method'=>['nullable','string','max:120'],
            'tracking_number'=>['nullable','string','max:120'],
            'admin_notes'=>['nullable','string','max:3000'],
        ]);

        if (in_array($data['status'],['shipped','delivered'],true) && !$order->fulfilled_at) {
            $data['fulfilled_at']=now();
        }

        $order->update($data);

        if ($data['status']==='cancelled' && $oldStatus!=='cancelled') {
            $inventory->restockCancelledOrder($order);
        }

        $fresh=$order->fresh();
        $notifications->orderUpdated($fresh,$oldStatus);

        if ($oldStatus!==$fresh->status) {
            $mail->order($fresh,$fresh->status);
        }

        if ($oldPaymentStatus!==$fresh->payment_status) {
            if ($fresh->payment_status==='failed') {
                $mail->order($fresh,'payment_failed');
            } elseif ($fresh->payment_status==='refunded' && $fresh->status!=='refunded') {
                $mail->order($fresh,'refunded');
            }
        }

        $this->audit($fresh,'order_updated');

        return back()->with('success','Order updated. Status, payment and notification workflow completed.');
    }

    public function approvePayment(Order $order, CustomerNotificationService $notifications, TransactionalMailService $mail)
    {
        $oldStatus=$order->status;
        abort_unless($order->payment_method==='bank_transfer',422);

        $order->update([
            'payment_verification_status'=>'approved',
            'payment_status'=>'paid',
            'payment_verified_at'=>now(),
            'payment_verified_by'=>auth()->id(),
            'payment_rejection_reason'=>null,
            'status'=>$order->status==='pending'?'confirmed':$order->status,
        ]);

        $this->audit($order,'bank_payment_approved');
        $order->refresh();
        $notifications->paymentApproved($order);
        $mail->order($order,'payment_approved');
        $notifications->orderUpdated($order,$oldStatus);

        return back()->with('success','Bank payment approved and order marked paid.');
    }

    public function rejectPayment(Request $request, Order $order, CustomerNotificationService $notifications, TransactionalMailService $mail)
    {
        abort_unless($order->payment_method==='bank_transfer',422);
        $data=$request->validate(['reason'=>'required|string|max:1000']);

        $order->update([
            'payment_verification_status'=>'rejected',
            'payment_status'=>'failed',
            'payment_verified_at'=>null,
            'payment_verified_by'=>auth()->id(),
            'payment_rejection_reason'=>$data['reason'],
        ]);

        $this->audit($order,'bank_payment_rejected');
        $notifications->paymentRejected($order);
        $mail->order($order,'payment_rejected',$data['reason']);

        return back()->with('success','Bank payment rejected.');
    }

    public function receipt(Order $order)
    {
        abort_unless($order->payment_receipt_path && Storage::disk('local')->exists($order->payment_receipt_path),404);

        return Storage::disk('local')->download(
            $order->payment_receipt_path,
            $order->order_number.'-payment-receipt.'.pathinfo($order->payment_receipt_path,PATHINFO_EXTENSION)
        );
    }

    private function orderNumber(string $prefix='SBA'): string
    {
        do {
            $number=$prefix.'-'.now()->format('ymd').'-'.strtoupper(Str::random(6));
        } while (Order::where('order_number',$number)->exists());

        return $number;
    }

    private function audit(Order $order,string $action): void
    {
        if (!Schema::hasTable('audit_logs')) return;

        AuditLog::create([
            'user_id'=>auth()->id(),
            'action'=>$action,
            'entity_type'=>'order',
            'entity_id'=>$order->id,
            'ip_address'=>request()->ip(),
            'meta'=>['order_number'=>$order->order_number],
        ]);
    }
}
