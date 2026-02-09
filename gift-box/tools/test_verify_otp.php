<?php
// Quick test script to call AuthController::verifyOtp
chdir(__DIR__ . '/../');
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

// Modify these values to match a test user/otp in your DB
$payload = [
    'email' => 'test@example.com',
    'otp' => '000000',
];

$request = Request::create('/api/verify-otp', 'POST', $payload);

$controller = new AuthController();

$response = $controller->verifyOtp($request);

// Print JSON response
echo "HTTP Status: " . $response->getStatusCode() . "\n";
echo $response->getContent() . "\n";
