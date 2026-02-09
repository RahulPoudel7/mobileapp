<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\User;

echo "\n========================================\n";
echo "eSewa Payment Verification Test\n";
echo "========================================\n\n";

// Get the latest order
$order = Order::orderByDesc('id')->first();

if (!$order) {
    echo "❌ No orders found in database\n";
    exit(1);
}

echo "Order Details:\n";
echo "- Order ID: " . $order->id . "\n";
echo "- Order Number: " . $order->order_number . "\n";
echo "- Transaction UUID: " . $order->transaction_uuid . "\n";
echo "- Total Amount: " . $order->total_amount . "\n";
echo "- Payment Method: " . $order->payment_method . "\n";
echo "- Payment Status BEFORE: " . ($order->payment_status ?? 'NULL') . "\n";
echo "- Order Status BEFORE: " . $order->status . "\n\n";

// Get or create a test user for authentication
$user = User::first();
if (!$user) {
    echo "Creating test user...\n";
    $user = User::create([
        'name' => 'Test User',
        'phone' => '9843123456',
        'password' => bcrypt('password'),
    ]);
    echo "✅ Test user created\n\n";
}

$token = $user->createToken('test')->plainTextToken;

echo "Testing Payment Verification Endpoint:\n";
echo "- Endpoint: POST /api/payment/verify\n";
echo "- Order ID: " . $order->id . "\n";
echo "- Amount: " . $order->total_amount . "\n";
echo "- RefId (Transaction UUID): " . $order->transaction_uuid . "\n\n";

// Simulate calling the verify endpoint
$client = new GuzzleHttp\Client();
try {
    $response = $client->post('http://127.0.0.1:8000/api/payment/verify', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ],
        'form_params' => [
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'refId' => $order->transaction_uuid,
        ],
    ]);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body:\n";
    
    $responseData = json_decode($response->getBody(), true);
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Refresh order to check if payment_status was updated
    $order->refresh();
    echo "Order Details AFTER Verification:\n";
    echo "- Payment Status: " . ($order->payment_status ?? 'NULL') . "\n";
    echo "- Order Status: " . $order->status . "\n";
    
    if ($order->payment_status === 'paid') {
        echo "\n✅ SUCCESS! Payment status updated to 'paid'\n";
        echo "✅ When the app shows order details now, it will display 'Payment: Paid'\n";
    } else {
        echo "\n❌ Payment status was NOT updated\n";
        echo "Check the response above for error details\n";
    }
    
} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "❌ Client Error (Status " . $e->getResponse()->getStatusCode() . "):\n";
    echo $e->getResponse()->getBody() . "\n";
} catch (\GuzzleHttp\Exception\RequestException $e) {
    echo "❌ Request Error:\n";
    echo $e->getMessage() . "\n";
    echo "\nMake sure the Laravel server is running:\n";
    echo "  php artisan serve\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
