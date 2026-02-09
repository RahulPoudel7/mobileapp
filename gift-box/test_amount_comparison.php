<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

echo "\n========================================\n";
echo "Testing Amount Mismatch Scenario\n";
echo "========================================\n\n";

// Get the latest order
$order = Order::orderByDesc('id')->first();

if (!$order) {
    echo "❌ No orders found\n";
    exit(1);
}

// Reset order to unpaid for testing
$order->update([
    'payment_status' => 'unpaid',
    'status' => 'pending'
]);

echo "Order Details:\n";
echo "- Order ID: " . $order->id . "\n";
echo "- Total Amount: " . $order->total_amount . " (DB format)\n";
echo "- Transaction UUID: " . $order->transaction_uuid . "\n";
echo "- Payment Status: " . $order->payment_status . "\n\n";

// Simulate eSewa callback with different decimal format
// eSewa sends "3997.0" while DB has "3997.00"
$esewaAmount = rtrim(rtrim((string)$order->total_amount, '0'), '.');

$callbackData = [
    'transaction_code' => '000E1ZZ',
    'status' => 'COMPLETE',
    'total_amount' => $esewaAmount, // eSewa format: "3997.0"
    'transaction_uuid' => $order->transaction_uuid,
    'product_code' => 'EPAYTEST',
    'signed_field_names' => 'transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names',
    'signature' => 'test_signature'
];

echo "Testing with different decimal formats:\n";
echo "- DB Amount: " . $order->total_amount . "\n";
echo "- eSewa Amount: " . $callbackData['total_amount'] . "\n";
echo "- Are they equal as floats? " . (floatval($order->total_amount) === floatval($callbackData['total_amount']) ? 'YES' : 'NO') . "\n\n";

// Convert to base64 encoded JSON (eSewa format)
$jsonData = json_encode($callbackData);
$base64Data = base64_encode($jsonData);

// Call the success endpoint
$url = 'http://127.0.0.1:8000/api/payment/esewa/success?data=' . urlencode($base64Data);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Callback Response:\n";
echo "- HTTP Status: $httpCode\n";
echo "- Response: $response\n\n";

// Check order status after
$order->refresh();
echo "Order Status After:\n";
echo "- Payment Status: " . $order->payment_status . "\n";
echo "- Order Status: " . $order->status . "\n\n";

if ($order->payment_status === 'paid' && $httpCode === 200) {
    echo "✅ SUCCESS! Amount comparison works correctly\n";
} else {
    echo "❌ FAILED! Payment status: " . $order->payment_status . "\n";
}
