<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Services\CustomerNotificationService;
use App\Services\OrderInventoryService;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders=Order::with('customer')->withCount('items')
            ->when($request->filled('q'),fn($q)=>$q->where(fn($x)=>$x->where('order_number','like','%'.$request->q.'%')->orWhere('customer_name','like','%'.$request->q.'%')->orWhere('customer_email','like','%'.$request->q.'%')))
            ->when($request->filled('status'),fn($q)=>$q->where('status',$request->status))
            ->when($request->filled('payment_status'),fn($q)=>$q->where('payment_status',$request->payment_status))
            ->latest('placed_at')->latest()->paginate(25)->withQueryString();
        return view('admin.orders.index',compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['customer','items.product','items.variant']);
        return view('admin.orders.show',compact('order'));
    }

    public function update(Request $request, Order $order, CustomerNotificationService $notifications, OrderInventoryService $inventory, TransactionalMailService $mail)
    {
        $oldStatus = $order->status;
        $data=$request->validate([
            'status'=>['required',Rule::in(['pending','confirmed','processing','shipped','delivered','cancelled','refunded'])],
            'payment_status'=>['required',Rule::in(['pending','paid','failed','refunded'])],
            'payment_method'=>['nullable','string','max:80'],'shipping_method'=>['nullable','string','max:120'],
            'tracking_number'=>['nullable','string','max:120'],'admin_notes'=>['nullable','string','max:3000'],
        ]);
        if(in_array($data['status'],['shipped','delivered'],true) && !$order->fulfilled_at)$data['fulfilled_at']=now();
        $order->update($data);
        if ($data['status'] === 'cancelled' && $oldStatus !== 'cancelled') $inventory->restockCancelledOrder($order);
        $fresh = $order->fresh();
        $notifications->orderUpdated($fresh, $oldStatus);
        if ($oldStatus !== $fresh->status) $mail->order($fresh, $fresh->status);
        return back()->with('success','Order updated.');
    }

    public function approvePayment(Order $order, CustomerNotificationService $notifications, TransactionalMailService $mail)
    {
        $oldStatus = $order->status;
        abort_unless($order->payment_method === 'bank_transfer', 422);
        $order->update([
            'payment_verification_status'=>'approved','payment_status'=>'paid','payment_verified_at'=>now(),
            'payment_verified_by'=>auth()->id(),'payment_rejection_reason'=>null,
            'status'=>$order->status === 'pending' ? 'confirmed' : $order->status,
        ]);
        $this->audit($order, 'bank_payment_approved');
        $order->refresh();
        $notifications->paymentApproved($order);
        $mail->order($order, 'payment_approved');
        $notifications->orderUpdated($order, $oldStatus);
        return back()->with('success','Bank payment approved and order marked paid.');
    }

    public function rejectPayment(Request $request, Order $order, CustomerNotificationService $notifications, TransactionalMailService $mail)
    {
        abort_unless($order->payment_method === 'bank_transfer', 422);
        $data=$request->validate(['reason'=>'required|string|max:1000']);
        $order->update([
            'payment_verification_status'=>'rejected','payment_status'=>'failed','payment_verified_at'=>null,
            'payment_verified_by'=>auth()->id(),'payment_rejection_reason'=>$data['reason'],
        ]);
        $this->audit($order, 'bank_payment_rejected');
        $notifications->paymentRejected($order);
        $mail->order($order, 'payment_rejected', $data['reason']);
        return back()->with('success','Bank payment rejected.');
    }

    public function receipt(Order $order)
    {
        abort_unless($order->payment_receipt_path && Storage::disk('local')->exists($order->payment_receipt_path), 404);
        return Storage::disk('local')->download($order->payment_receipt_path, $order->order_number.'-payment-receipt.'.pathinfo($order->payment_receipt_path, PATHINFO_EXTENSION));
    }

    private function audit(Order $order, string $action): void
    {
        if (!Schema::hasTable('audit_logs')) return;
        AuditLog::create(['user_id'=>auth()->id(),'action'=>$action,'entity_type'=>'order','entity_id'=>$order->id,'ip_address'=>request()->ip(),'meta'=>['order_number'=>$order->order_number]]);
    }
}
