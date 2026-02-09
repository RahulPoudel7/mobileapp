<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

echo "\n========================================\n";
echo "Testing eSewa Payment Callback\n";
echo "========================================\n\n";

// Get the latest order
$order = Order::orderByDesc('id')->first();

if (!$order) {
    echo "❌ No orders found\n";
    exit(1);
}

echo "Order Details:\n";
echo "- Order ID: " . $order->id . "\n";
echo "- Total Amount: " . $order->total_amount . "\n";
echo "- Transaction UUID: " . $order->transaction_uuid . "\n";
echo "- Payment Status BEFORE: " . ($order->payment_status ?? 'NULL') . "\n";
echo "- Order Status BEFORE: " . $order->status . "\n\n";

// Simulate eSewa callback data
$callbackData = [
    'transaction_code' => '000E1ZZ',
    'status' => 'COMPLETE',
    'total_amount' => (string)$order->total_amount, // eSewa sends as "3997.0"
    'transaction_uuid' => $order->transaction_uuid,
    'product_code' => 'EPAYTEST',
    'signed_field_names' => 'transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names',
    'signature' => 'test_signature'
];

// Convert to base64 encoded JSON (eSewa format)
$jsonData = json_encode($callbackData);
$base64Data = base64_encode($jsonData);

echo "Simulating eSewa Callback:\n";
echo "- Status: COMPLETE\n";
echo "- Amount: " . $callbackData['total_amount'] . "\n\n";

// Call the success endpoint
$url = 'http://127.0.0.1:8000/api/payment/esewa/success?data=' . urlencode($base64Data);

echo "Calling: $url\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

// Check order status after
$order->refresh();
echo "Order Status After:\n";
echo "- Payment Status: " . ($order->payment_status ?? 'NULL') . "\n";
echo "- Order Status: " . $order->status . "\n\n";

if ($order->payment_status === 'paid') {
    echo "✅ SUCCESS! Payment marked as paid\n";
} else {
    echo "❌ FAILED! Payment status is still: " . $order->payment_status . "\n";
}
