<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

echo "\n========================================\n";
echo "Direct Payment Status Update Test\n";
echo "========================================\n\n";

// Get the latest order
$order = Order::orderByDesc('id')->first();

if (!$order) {
    echo "❌ No orders found in database\n";
    exit(1);
}

echo "Order Before Update:\n";
echo "- Order ID: " . $order->id . "\n";
echo "- Payment Status: " . ($order->payment_status ?? 'NULL') . "\n";
echo "- Order Status: " . $order->status . "\n\n";

// Directly update the order as if payment verification succeeded
echo "Updating order payment_status to 'paid'...\n\n";
$order->update([
    'status' => 'confirmed',
    'payment_status' => 'paid'
]);

// Refresh to see the changes
$order->refresh();

echo "Order After Update:\n";
echo "- Order ID: " . $order->id . "\n";
echo "- Payment Status: " . ($order->payment_status ?? 'NULL') . "\n";
echo "- Order Status: " . $order->status . "\n\n";

if ($order->payment_status === 'paid') {
    echo "✅ SUCCESS! Payment status is now 'paid'\n";
    echo "✅ When you view order details in the app, it will show 'Payment: Paid' in green\n";
} else {
    echo "❌ Update failed\n";
}

echo "\n========================================\n";
