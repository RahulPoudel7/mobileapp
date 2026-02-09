<?php
/**
 * Comprehensive Payment System Test
 * Tests: Database structure, API routes, payment service, and eSewa integration
 */

chdir(__DIR__);
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Http\Controllers\Api\PaymentApiController;

echo "\n========================================\n";
echo "   GIFT-BOX PAYMENT SYSTEM TEST\n";
echo "========================================\n\n";

// Test 1: Check if Orders table exists and has required columns
echo "✓ TEST 1: Database Schema Verification\n";
echo "-------------------------------------------\n";

if (!Schema::hasTable('orders')) {
    echo "❌ CRITICAL: 'orders' table NOT FOUND!\n";
} else {
    echo "✓ 'orders' table exists\n";
    
    $requiredColumns = [
        'id', 'user_id', 'transaction_uuid', 'subtotal', 'delivery_charge',
        'total_amount', 'distance_km', 'payment_method', 'payment_status',
        'status', 'gift_wrapping_fee', 'personal_note_fee', 'personal_note_text',
        'has_personal_note', 'has_gift_wrapping'
    ];
    
    foreach ($requiredColumns as $col) {
        if (Schema::hasColumn('orders', $col)) {
            echo "  ✓ Column '{$col}' exists\n";
        } else {
            echo "  ❌ Column '{$col}' MISSING!\n";
        }
    }
}

// Test 2: Check API Routes
echo "\n✓ TEST 2: API Routes Verification\n";
echo "-------------------------------------------\n";

$routeFile = file_get_contents(__DIR__ . '/routes/api.php');
if (strpos($routeFile, 'PaymentApiController') !== false) {
    echo "✓ PaymentApiController is imported\n";
    if (strpos($routeFile, "/payment/verify") !== false) {
        echo "✓ POST /payment/verify route is defined\n";
    } else {
        echo "❌ POST /payment/verify route NOT found!\n";
    }
} else {
    echo "❌ PaymentApiController NOT imported in routes!\n";
}

// Test 3: Check eSewa Configuration
echo "\n✓ TEST 3: eSewa Configuration\n";
echo "-------------------------------------------\n";

$merchantId = env('ESEWA_MERCHANT_ID');
$secretKey = env('ESEWA_SECRET_KEY');
$verifyUrl = env('ESEWA_VERIFY_URL');

echo "Merchant ID: " . ($merchantId ? "✓ {$merchantId}" : "❌ NOT SET") . "\n";
echo "Secret Key: " . ($secretKey ? "✓ SET" : "❌ NOT SET") . "\n";
echo "Verify URL: " . ($verifyUrl ? "✓ {$verifyUrl}" : "❌ NOT SET") . "\n";

// Check if it's the correct endpoint
if ($verifyUrl === 'https://uat.esewa.com.np/api/epay/transaction/status') {
    echo "⚠️  WARNING: Using OLD eSewa UAT endpoint (uat.esewa.com.np)\n";
    echo "   Consider updating to: https://rc-epay.esewa.com.np/api/epay/transaction/status\n";
} elseif ($verifyUrl === 'https://rc-epay.esewa.com.np/api/epay/transaction/status') {
    echo "✓ Using NEW eSewa RC endpoint (rc-epay.esewa.com.np)\n";
}

// Test 4: Check EsewaService
echo "\n✓ TEST 4: EsewaService Class\n";
echo "-------------------------------------------\n";

if (class_exists('App\Services\EsewaService')) {
    echo "✓ EsewaService class exists\n";
    $reflection = new ReflectionClass('App\Services\EsewaService');
    if ($reflection->hasMethod('verifyPayment')) {
        echo "✓ verifyPayment() method exists\n";
    } else {
        echo "❌ verifyPayment() method NOT found!\n";
    }
} else {
    echo "❌ EsewaService class NOT found!\n";
}

// Test 5: Check PaymentApiController
echo "\n✓ TEST 5: PaymentApiController\n";
echo "-------------------------------------------\n";

if (class_exists('App\Http\Controllers\Api\PaymentApiController')) {
    echo "✓ PaymentApiController class exists\n";
    $reflection = new ReflectionClass('App\Http\Controllers\Api\PaymentApiController');
    if ($reflection->hasMethod('verifyEsewa')) {
        echo "✓ verifyEsewa() method exists\n";
    } else {
        echo "❌ verifyEsewa() method NOT found!\n";
    }
} else {
    echo "❌ PaymentApiController class NOT found!\n";
}

// Test 6: Check OrderController for payment URL generation
echo "\n✓ TEST 6: OrderController Payment Logic\n";
echo "-------------------------------------------\n";

if (class_exists('App\Http\Controllers\Api\OrderController')) {
    echo "✓ OrderController class exists\n";
    $reflection = new ReflectionClass('App\Http\Controllers\Api\OrderController');
    if ($reflection->hasMethod('store')) {
        echo "✓ store() method exists (creates orders with payment)\n";
    } else {
        echo "❌ store() method NOT found!\n";
    }
} else {
    echo "❌ OrderController class NOT found!\n";
}

// Test 7: Sample signature generation
echo "\n✓ TEST 7: Test Signature Generation\n";
echo "-------------------------------------------\n";

$testAmount = 2080.00;
$testUuid = '1000-1-abcd';
$testProductCode = 'EPAYTEST';
$testSecretKey = '8gBm/:&EnhH.1/q';

$message = "total_amount={$testAmount},transaction_uuid={$testUuid},product_code={$testProductCode}";
$signature = base64_encode(hash_hmac('sha256', $message, $testSecretKey, true));

echo "Sample Order Details:\n";
echo "  Amount: {$testAmount}\n";
echo "  Transaction UUID: {$testUuid}\n";
echo "  Product Code: {$testProductCode}\n";
echo "  Generated Signature: {$signature}\n";
echo "✓ Signature generated successfully\n";

// Test 8: Check database connection
echo "\n✓ TEST 8: Database Connection\n";
echo "-------------------------------------------\n";

try {
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n";
    
    // Check if there are any orders
    $orderCount = DB::table('orders')->count();
    echo "✓ Total orders in database: {$orderCount}\n";
    
    // Check for orders with payment info
    $paidOrders = DB::table('orders')->where('payment_status', 'paid')->count();
    echo "✓ Paid orders: {$paidOrders}\n";
    
    $pendingOrders = DB::table('orders')->where('payment_status', 'unpaid')->count();
    echo "✓ Unpaid (pending) orders: {$pendingOrders}\n";
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 9: Summary and Recommendations
echo "\n✓ TEST 9: Summary\n";
echo "-------------------------------------------\n";

echo "PAYMENT FLOW:\n";
echo "1. Client creates order → OrderController::store()\n";
echo "2. Order saved with 'unpaid' payment_status\n";
echo "3. Client receives eSewa payment URL + signature\n";
echo "4. User completes payment on eSewa\n";
echo "5. Client sends verification → PaymentApiController::verifyEsewa()\n";
echo "6. Server verifies with eSewa\n";
echo "7. Order status updated to 'confirmed', payment_status to 'paid'\n";

echo "\n========================================\n";
echo "✓ PAYMENT SYSTEM TEST COMPLETE\n";
echo "========================================\n\n";

?>
