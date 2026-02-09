<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

// Get the latest order (Order ID 51 from previous check)
$order = Order::find(51);

if (!$order) {
    echo "Order not found\n";
    exit(1);
}

echo "Testing Payment Verification\n";
echo "=============================\n";
echo "Order ID: " . $order->id . "\n";
echo "Order Number: " . $order->order_number . "\n";
echo "Transaction UUID: " . $order->transaction_uuid . "\n";
echo "Total Amount: " . $order->total_amount . "\n";
echo "Payment Status Before: " . ($order->payment_status ?? 'NULL') . "\n\n";

// Simulate calling the verify endpoint
$verifyUrl = 'http://localhost:8000/api/payment/verify';

// Get auth token (you need to create a user or use existing token)
$user = \App\Models\User::first();
if (!$user) {
    echo "No user found. Creating test user...\n";
    $user = \App\Models\User::create([
        'name' => 'Test User',
        'phone' => '9843123456',
        'password' => bcrypt('password'),
    ]);
}

$token = $user->createToken('test')->plainTextToken;

echo "Test URL: " . $verifyUrl . "\n";
echo "Order ID: " . $order->id . "\n";
echo "Amount: " . $order->total_amount . "\n";
echo "Transaction UUID: " . $order->transaction_uuid . "\n\n";

// Make the verification request
$client = new GuzzleHttp\Client();
try {
    $response = $client->post($verifyUrl, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'form_params' => [
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'refId' => $order->transaction_uuid,
        ],
    ]);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body:\n";
    echo json_encode(json_decode($response->getBody()), JSON_PRETTY_PRINT) . "\n\n";
    
    // Refresh order to check if payment_status was updated
    $order->refresh();
    echo "Payment Status After: " . ($order->payment_status ?? 'NULL') . "\n";
    echo "Status After: " . $order->status . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e instanceof \GuzzleHttp\Exception\ClientException) {
        echo "Response: " . $e->getResponse()->getBody() . "\n";
    }
}
