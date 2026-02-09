<?php

/**
 * Test Script: Place an eSewa Order
 * This script simulates placing an order through the API with eSewa payment
 */

$baseUrl = 'http://127.0.0.1:8000/api';

echo "=== eSewa Order Test ===\n\n";

// Step 1: Register a test user (or skip if you already have one)
echo "Step 1: Registering test user...\n";
$registerData = [
    'name' => 'Test User ' . rand(1000, 9999),
    'email' => 'testuser' . rand(1000, 9999) . '@test.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'phone' => '98' . rand(10000000, 99999999)
];

$ch = curl_init($baseUrl . '/users');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
$registerResponse = curl_exec($ch);
$registerResult = json_decode($registerResponse, true);
curl_close($ch);

if (isset($registerResult['success']) && $registerResult['success']) {
    echo "✓ User registered: " . $registerData['email'] . "\n";
    $userId = $registerResult['data']['user']['id'] ?? null;
} else {
    echo "✗ Registration failed. Trying to login instead...\n";
    $registerData['email'] = 'admin@admin.com';
    $registerData['password'] = 'admin123';
}

// Step 2: Get OTP (if needed) - Skip for now and use login directly
echo "\nStep 2: Logging in...\n";
$loginData = [
    'email' => $registerData['email'],
    'password' => $registerData['password']
];

$ch = curl_init($baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
$loginResponse = curl_exec($ch);
$loginResult = json_decode($loginResponse, true);
curl_close($ch);

if (isset($loginResult['success']) && $loginResult['success']) {
    echo "✓ Login successful (OTP sent to phone)\n";
    $userId = $loginResult['data']['user_id'] ?? null;
    
    // For testing, we'll need to verify OTP
    echo "\n⚠ OTP verification required. Enter OTP code: ";
    $otp = trim(fgets(STDIN));
    
    echo "Verifying OTP...\n";
    $otpData = [
        'user_id' => $userId,
        'otp' => $otp
    ];
    
    $ch = curl_init($baseUrl . '/verify-otp');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($otpData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    $otpResponse = curl_exec($ch);
    $otpResult = json_decode($otpResponse, true);
    curl_close($ch);
    
    if (isset($otpResult['success']) && $otpResult['success']) {
        echo "✓ OTP verified successfully\n";
        $token = $otpResult['data']['token'] ?? null;
    } else {
        echo "✗ OTP verification failed: " . ($otpResult['message'] ?? 'Unknown error') . "\n";
        echo "Response: " . print_r($otpResult, true) . "\n";
        exit(1);
    }
} else {
    echo "✗ Login failed: " . ($loginResult['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

// Step 3: Get available gifts
echo "\nStep 3: Fetching available gifts...\n";
$ch = curl_init($baseUrl . '/gifts');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
$giftsResponse = curl_exec($ch);
$giftsResult = json_decode($giftsResponse, true);
curl_close($ch);

if (isset($giftsResult['success']) && $giftsResult['success'] && !empty($giftsResult['data'])) {
    $gift = $giftsResult['data'][0];
    echo "✓ Found gift: {$gift['name']} (ID: {$gift['id']}, Price: Rs. {$gift['price']})\n";
    $giftId = $gift['id'];
    $giftPrice = $gift['price'];
} else {
    echo "✗ No gifts found. Please add gifts first.\n";
    exit(1);
}

// Step 4: Place eSewa order
echo "\nStep 4: Placing eSewa order...\n";
$orderData = [
    'recipient_name' => 'John Doe',
    'recipient_phone' => '9841234567',
    'delivery_address' => 'Thamel, Kathmandu',
    'delivery_lat' => 27.7172,
    'delivery_lng' => 85.3240,
    'payment_method' => 'esewa',
    'has_personal_note' => true,
    'personal_note_text' => 'Happy Birthday! Enjoy your gift.',
    'has_gift_wrapping' => true,
    'items' => [
        [
            'gift_id' => $giftId,
            'quantity' => 2
        ]
    ]
];

$ch = curl_init($baseUrl . '/orders');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
$orderResponse = curl_exec($ch);
$orderResult = json_decode($orderResponse, true);
curl_close($ch);

if (isset($orderResult['success']) && $orderResult['success']) {
    echo "✓ Order created successfully!\n\n";
    echo "Order Details:\n";
    echo "  Order ID: " . $orderResult['data']['order_id'] . "\n";
    echo "  Transaction UUID: " . $orderResult['data']['transaction_uuid'] . "\n";
    echo "  Total Amount: Rs. " . $orderResult['data']['total_amount'] . "\n";
    echo "  Status: " . $orderResult['data']['status'] . "\n\n";
    
    echo "eSewa Payment URL:\n";
    echo $orderResult['data']['esewa_payment_url'] . "\n\n";
    
    echo "📱 Next Steps:\n";
    echo "1. Open the URL above in your browser\n";
    echo "2. Use eSewa test credentials to complete payment\n";
    echo "3. After payment, check the admin panel to verify payment status cannot be changed\n\n";
    
    // Save order ID for later reference
    file_put_contents('last_esewa_order.txt', $orderResult['data']['order_id']);
    echo "Order ID saved to: last_esewa_order.txt\n";
} else {
    echo "✗ Order creation failed: " . ($orderResult['message'] ?? 'Unknown error') . "\n";
    echo "Response: " . print_r($orderResult, true) . "\n";
}

echo "\n=== Test Complete ===\n";
