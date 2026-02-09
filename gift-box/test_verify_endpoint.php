<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\User;

echo "\n========================================\n";
echo "Testing Payment Verify Endpoint\n";
echo "========================================\n\n";

// Get the latest order
$order = Order::orderByDesc('id')->first();
$user = User::find($order->user_id);

if (!$order || !$user) {
    echo "❌ No order or user found\n";
    exit(1);
}

echo "Order Details:\n";
echo "- Order ID: " . $order->id . "\n";
echo "- Total Amount: " . $order->total_amount . "\n";
echo "- Transaction UUID: " . $order->transaction_uuid . "\n\n";

// Simulate API call
$url = 'http://127.0.0.1:8000/api/payment/verify';
$token = $user->createToken('test')->plainTextToken;

$data = [
    'order_id' => $order->id,
    'amount' => $order->total_amount,
    'refId' => $order->transaction_uuid
];

echo "Making POST request to: $url\n";
echo "Data: " . json_encode($data) . "\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

// Check order status after
$order->refresh();
echo "Order Status After:\n";
echo "- Payment Status: " . $order->payment_status . "\n";
echo "- Order Status: " . $order->status . "\n";
