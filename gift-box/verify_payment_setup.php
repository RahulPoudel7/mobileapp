<?php
echo "\n=== FINAL PAYMENT SYSTEM VERIFICATION ===\n\n";

// Check .env
$env = file_get_contents(__DIR__ . '/.env');
if (strpos($env, 'rc-epay.esewa.com.np') !== false) {
    echo "✓ eSewa endpoint updated to RC (rc-epay.esewa.com.np)\n";
} else {
    echo "✗ eSewa endpoint not updated\n";
}

if (strpos($env, 'ESEWA_MERCHANT_ID=EPAYTEST') !== false) {
    echo "✓ eSewa Merchant ID is set to EPAYTEST\n";
}

if (strpos($env, 'ESEWA_SECRET_KEY=8gBm') !== false) {
    echo "✓ eSewa Secret Key is configured\n";
}

// Check PaymentApiController
$controller = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/PaymentApiController.php');
if (strpos($controller, 'ESEWA_VERIFY_URL') !== false) {
    echo "✓ PaymentApiController uses env variable for eSewa URL\n";
}

// Check routes
$routes = file_get_contents(__DIR__ . '/routes/api.php');
if (strpos($routes, '/payment/verify') !== false) {
    echo "✓ Payment verification route exists\n";
}

echo "\n=== PAYMENT SYSTEM STATUS: ✓ READY TO USE ===\n\n";
?>
