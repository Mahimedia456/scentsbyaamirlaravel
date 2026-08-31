<?php

namespace App\Services;

use App\Models\CustomerNotification;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;

class CustomerNotificationService
{
    public function orderPlaced(Order $order): void
    {
        $this->create($order, 'order_placed', 'Order received', "Your order {$order->order_number} has been received.");
    }

    public function orderUpdated(Order $order, ?string $oldStatus = null): void
    {
        if ($oldStatus === $order->status) return;
        $labels = [
            'confirmed'=>'Order confirmed','processing'=>'Order is being prepared','shipped'=>'Order shipped',
            'delivered'=>'Order delivered','cancelled'=>'Order cancelled','refunded'=>'Order refunded',
        ];
        if (isset($labels[$order->status])) {
            $this->create($order, 'order_status', $labels[$order->status], "Order {$order->order_number} is now {$order->status}.");
        }
    }

    public function paymentApproved(Order $order): void
    {
        $this->create($order, 'payment_approved', 'Payment verified', "Bank payment for {$order->order_number} has been approved.");
    }

    public function paymentRejected(Order $order): void
    {
        $this->create($order, 'payment_rejected', 'Payment needs attention', "Bank payment for {$order->order_number} could not be verified. Please review your order details.");
    }

    private function create(Order $order, string $type, string $title, string $message): void
    {
        if (!Schema::hasTable('customer_notifications') || !$order->customer_id) return;
        CustomerNotification::create([
            'customer_id'=>$order->customer_id,'order_id'=>$order->id,'type'=>$type,
            'title'=>$title,'message'=>$message,'data'=>['order_number'=>$order->order_number,'status'=>$order->status,'payment_status'=>$order->payment_status],
        ]);
    }
}
