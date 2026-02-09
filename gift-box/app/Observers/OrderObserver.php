<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Notification;

class OrderObserver
{
    /**
     * Handle the Order "updating" event.
     * Detects status changes and creates notifications automatically.
     */
    public function updating(Order $order)
    {
        // Check if status is being changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;

            // Only create notification if status actually changed
            if ($oldStatus !== $newStatus) {
                $this->createOrderStatusNotification($order, $oldStatus, $newStatus);
            }
        }
    }

    /**
     * Create notification for order status change
     */
    private function createOrderStatusNotification($order, $oldStatus, $newStatus)
    {
        $orderNumber = 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
        
        // Define notification messages based on status
        $notifications = [
            'accepted' => [
                'title' => 'Order Confirmed',
                'message' => "Your order {$orderNumber} has been confirmed and is being prepared.",
            ],
            'processing' => [
                'title' => 'Order Processing',
                'message' => "Your order {$orderNumber} is now being processed.",
            ],
            'shipped' => [
                'title' => 'Order Shipped',
                'message' => "Great news! Your order {$orderNumber} has been shipped and is on its way.",
            ],
            'delivered' => [
                'title' => 'Order Delivered',
                'message' => "Your order {$orderNumber} has been successfully delivered. Thank you for your purchase!",
            ],
            'cancelled' => [
                'title' => 'Order Cancelled',
                'message' => "Your order {$orderNumber} has been cancelled.",
            ],
        ];

        // Only create notification if status has a notification message
        if (isset($notifications[$newStatus])) {
            Notification::createForUser(
                $order->user_id,
                $notifications[$newStatus]['title'],
                $notifications[$newStatus]['message'],
                'order',
                ['order_id' => $order->id, 'order_number' => $orderNumber]
            );
        }
    }

    /**
     * Handle the Order "updated" event.
     * Update delivered_at timestamp when status becomes delivered.
     */
    public function updated(Order $order)
    {
        // If status changed to delivered, set delivered_at timestamp
        if ($order->status === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
            $order->saveQuietly(); // Save without triggering observer again
        }
    }
}
