<?php

/**
 * Quick Test: Create eSewa Order Directly in Database
 * This bypasses API authentication for testing purposes
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gift;
use App\Models\Order;
use Illuminate\Support\Str;

echo "=== Creating Test eSewa Order ===\n\n";

// Get or create a test user
$user = User::where('email', 'admin@admin.com')->first();
if (!$user) {
    $user = User::where('email', 'test@example.com')->first();
}
if (!$user) {
    echo "Creating test user...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'testuser' . time() . '@example.com',
        'password' => bcrypt('password123'),
        'phone' => '9841234567',
        'role' => 'user'
    ]);
}
echo "✓ Using user: {$user->email} (ID: {$user->id})\n";

// Get a gift
$gift = Gift::first();
if (!$gift) {
    echo "✗ No gifts found. Please add gifts first using:\n";
    echo "  php artisan db:seed\n";
    exit(1);
}
echo "✓ Using gift: {$gift->name} (ID: {$gift->id}, Price: Rs. {$gift->price})\n\n";

// Calculate order totals
$quantity = 2;
$subtotal = $gift->price * $quantity;
$personalNoteFee = 100.00;
$giftWrappingFee = 100.00;
$deliveryCharge = 150.00; // Sample delivery charge
$totalAmount = $subtotal + $personalNoteFee + $giftWrappingFee + $deliveryCharge;

echo "Order Breakdown:\n";
echo "  Subtotal: Rs. " . number_format($subtotal, 2) . "\n";
echo "  Personal Note Fee: Rs. " . number_format($personalNoteFee, 2) . "\n";
echo "  Gift Wrapping Fee: Rs. " . number_format($giftWrappingFee, 2) . "\n";
echo "  Delivery Charge: Rs. " . number_format($deliveryCharge, 2) . "\n";
echo "  Total: Rs. " . number_format($totalAmount, 2) . "\n\n";

// Create order with eSewa payment
$transactionUuid = time() . '-' . $user->id . '-' . Str::random(4);

$order = Order::create([
    'user_id' => $user->id,
    'transaction_uuid' => $transactionUuid,
    'subtotal' => $subtotal,
    'personal_note_fee' => $personalNoteFee,
    'gift_wrapping_fee' => $giftWrappingFee,
    'delivery_charge' => $deliveryCharge,
    'total_amount' => $totalAmount,
    'distance_km' => 5.5,
    'payment_method' => 'esewa',
    'recipient_name' => 'John Doe',
    'recipient_phone' => '9841234567',
    'delivery_address' => 'Thamel, Kathmandu, Nepal',
    'status' => 'pending',
    'payment_status' => 'unpaid',
    'has_personal_note' => true,
    'personal_note_text' => 'Happy Birthday! Enjoy your gift.',
    'has_gift_wrapping' => true,
    'quantity' => $quantity
]);

// Add order items
$order->items()->create([
    'gift_id' => $gift->id,
    'quantity' => $quantity,
    'price' => $gift->price,
]);

echo "✓ Order created successfully!\n\n";
echo "Order Details:\n";
echo "  Order ID: #" . $order->id . "\n";
echo "  Order Number: ORD-" . str_pad($order->id, 5, '0', STR_PAD_LEFT) . "\n";
echo "  Transaction UUID: " . $order->transaction_uuid . "\n";
echo "  Payment Method: eSewa\n";
echo "  Payment Status: " . $order->payment_status . "\n";
echo "  Total Amount: Rs. " . number_format($order->total_amount, 2) . "\n\n";

// Simulate eSewa payment completion
echo "Marking order as PAID (simulating successful eSewa payment)...\n";
$order->update(['payment_status' => 'paid']);
echo "✓ Order marked as PAID\n\n";

echo "📋 Next Steps:\n";
echo "1. Login to admin panel at: http://127.0.0.1:8000/login\n";
echo "   Email: admin@admin.com\n";
echo "   Password: admin123\n\n";
echo "2. Go to Orders section\n";
echo "3. Find Order #ORD-" . str_pad($order->id, 5, '0', STR_PAD_LEFT) . "\n";
echo "4. Try to edit the payment status - it should be DISABLED for eSewa payments!\n\n";

echo "Order ID saved for reference: {$order->id}\n";
file_put_contents('last_test_order_id.txt', $order->id);

echo "\n=== Test Complete ===\n";
