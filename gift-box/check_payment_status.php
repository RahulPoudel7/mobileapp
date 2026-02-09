<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = \App\Models\Order::orderByDesc('id')->first();

echo "Latest Order Details:\n";
echo "======================\n";
echo "Order ID: " . $order->id . "\n";
echo "Order Number: " . $order->order_number . "\n";
echo "Payment Status: " . ($order->payment_status ?? 'NULL') . "\n";
echo "Status: " . $order->status . "\n";
echo "Payment Method: " . $order->payment_method . "\n";
echo "Transaction UUID: " . $order->transaction_uuid . "\n";
echo "Total Amount: " . $order->total_amount . "\n";
echo "Created At: " . $order->created_at . "\n";
echo "======================\n";
