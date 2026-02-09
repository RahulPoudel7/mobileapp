<?php

/**
 * Test Order Status Notification System
 * 
 * This script demonstrates how to update order status and automatically
 * create notifications for users.
 * 
 * Usage:
 * 1. First create an order through the app
 * 2. Run: php test_order_notification.php <order_id> <new_status>
 * 
 * Example:
 * php test_order_notification.php 1 shipped
 * 
 * Valid statuses: pending, accepted, processing, shipped, delivered, cancelled
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\Notification;

// Get command line arguments
$orderId = $argv[1] ?? null;
$newStatus = $argv[2] ?? null;

if (!$orderId || !$newStatus) {
    echo "❌ Usage: php test_order_notification.php <order_id> <new_status>\n";
    echo "\nValid statuses: pending, accepted, processing, shipped, delivered, cancelled\n";
    exit(1);
}

// Validate status
$validStatuses = ['pending', 'accepted', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    echo "❌ Invalid status. Valid options: " . implode(', ', $validStatuses) . "\n";
    exit(1);
}

// Find the order
$order = Order::find($orderId);
if (!$order) {
    echo "❌ Order #$orderId not found\n";
    exit(1);
}

$oldStatus = $order->status;
$orderNumber = 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

echo "\n📦 Order Status Update\n";
echo "═══════════════════════════════════════\n";
echo "Order ID: $orderId\n";
echo "Order Number: $orderNumber\n";
echo "User ID: {$order->user_id}\n";
echo "Old Status: $oldStatus\n";
echo "New Status: $newStatus\n";
echo "═══════════════════════════════════════\n\n";

// Update status
$order->update(['status' => $newStatus]);

// Update delivered_at if delivered
if ($newStatus === 'delivered' && !$order->delivered_at) {
    $order->update(['delivered_at' => now()]);
}

// Define notification messages
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

// Create notification
if (isset($notifications[$newStatus]) && $oldStatus !== $newStatus) {
    $notification = Notification::createForUser(
        $order->user_id,
        $notifications[$newStatus]['title'],
        $notifications[$newStatus]['message'],
        'order',
        ['order_id' => $order->id, 'order_number' => $orderNumber]
    );

    echo "✅ Order status updated: $oldStatus → $newStatus\n";
    echo "✅ Notification created:\n";
    echo "   📧 Title: {$notification->title}\n";
    echo "   📝 Message: {$notification->message}\n";
    echo "   🔔 Type: {$notification->type}\n";
    echo "   👤 User ID: {$notification->user_id}\n\n";

    // Show unread count
    $unreadCount = Notification::where('user_id', $order->user_id)
        ->where('is_read', false)
        ->count();
    echo "🔔 User has $unreadCount unread notification(s)\n\n";
} else {
    echo "⚠️  No notification created (status unchanged or no notification defined)\n\n";
}

echo "✨ Done!\n";
echo "\nTo test in the app:\n";
echo "1. Login as user ID {$order->user_id}\n";
echo "2. Check the notification bell icon for badge count\n";
echo "3. Open NotificationActivity to see the notification\n\n";
