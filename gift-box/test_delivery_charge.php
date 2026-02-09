<?php

/**
 * Test script for delivery charge calculation logic
 * 
 * Business Rules:
 * - If subtotal >= 5000: free delivery (Rs. 0)
 * - 0–3 km: Rs. 80
 * - 3–7 km: Rs. 120
 * - 7–12 km: Rs. 160
 * - 12+ km: not allowed
 */

function calculateDeliveryCharge(float $subtotal, float $distanceKm): float
{
    // Free delivery for orders over 5000
    if ($subtotal >= 5000) {
        return 0.0;
    }

    // Distance-based pricing
    if ($distanceKm <= 3) {
        return 80.0;
    } elseif ($distanceKm <= 7) {
        return 120.0;
    } else {
        // 7–12 km
        return 160.0;
    }
}

// Test cases
$tests = [
    // Test 1: Free delivery for large orders
    ['subtotal' => 5000, 'distance' => 5, 'expected' => 0.0, 'description' => 'Free delivery for subtotal >= 5000'],
    ['subtotal' => 6000, 'distance' => 10, 'expected' => 0.0, 'description' => 'Free delivery for subtotal > 5000'],
    
    // Test 2: 0-3 km
    ['subtotal' => 1000, 'distance' => 1, 'expected' => 80.0, 'description' => '0-3 km: Rs. 80'],
    ['subtotal' => 2500, 'distance' => 2.5, 'expected' => 80.0, 'description' => '0-3 km (2.5 km): Rs. 80'],
    ['subtotal' => 3000, 'distance' => 3, 'expected' => 80.0, 'description' => '0-3 km (exactly 3 km): Rs. 80'],
    
    // Test 3: 3-7 km
    ['subtotal' => 1500, 'distance' => 3.1, 'expected' => 120.0, 'description' => '3-7 km (3.1 km): Rs. 120'],
    ['subtotal' => 2000, 'distance' => 5, 'expected' => 120.0, 'description' => '3-7 km (5 km): Rs. 120'],
    ['subtotal' => 2500, 'distance' => 7, 'expected' => 120.0, 'description' => '3-7 km (exactly 7 km): Rs. 120'],
    
    // Test 4: 7-12 km
    ['subtotal' => 1000, 'distance' => 7.1, 'expected' => 160.0, 'description' => '7-12 km (7.1 km): Rs. 160'],
    ['subtotal' => 2000, 'distance' => 10, 'expected' => 160.0, 'description' => '7-12 km (10 km): Rs. 160'],
    ['subtotal' => 3000, 'distance' => 12, 'expected' => 160.0, 'description' => '7-12 km (exactly 12 km): Rs. 160'],
    
    // Test 5: Edge cases
    ['subtotal' => 4999, 'distance' => 0, 'expected' => 80.0, 'description' => 'Just under 5000 (0 km): Rs. 80'],
    ['subtotal' => 5000.01, 'distance' => 1, 'expected' => 0.0, 'description' => 'Just over 5000 (1 km): Free delivery'],
];

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "  DELIVERY CHARGE CALCULATION - TEST RESULTS\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    $result = calculateDeliveryCharge($test['subtotal'], $test['distance']);
    $success = $result === $test['expected'];
    
    if ($success) {
        $passed++;
        echo "✓ PASS: {$test['description']}\n";
    } else {
        $failed++;
        echo "✗ FAIL: {$test['description']}\n";
    }
    
    echo "  Subtotal: Rs. {$test['subtotal']}, Distance: {$test['distance']} km\n";
    echo "  Expected: Rs. {$test['expected']}, Got: Rs. {$result}\n\n";
}

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "  SUMMARY: {$passed} passed, {$failed} failed\n";
echo "═══════════════════════════════════════════════════════════════════════════\n";

if ($failed === 0) {
    echo "\n🎉 All tests passed!\n";
    exit(0);
} else {
    echo "\n❌ Some tests failed!\n";
    exit(1);
}
