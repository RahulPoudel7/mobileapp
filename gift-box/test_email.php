<?php
// Test email configuration
chdir(__DIR__);
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

try {
    echo "Testing email configuration...\n";
    echo "Mail Driver: " . config('mail.default') . "\n";
    echo "Mail Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "Mail Port: " . config('mail.mailers.smtp.port') . "\n";
    echo "Mail Username: " . config('mail.mailers.smtp.username') . "\n";
    echo "Mail Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
    echo "\n";

    echo "Sending test OTP email...\n";
    Mail::to('rahul.rma33@gmail.com')->send(new SendOtpMail(
        'rahul.rma33@gmail.com',
        '123456',
        'Test User'
    ));
    
    echo "✅ Email sent successfully!\n";
} catch (\Exception $e) {
    echo "❌ Error sending email:\n";
    echo $e->getMessage() . "\n";
    echo "\nFull trace:\n";
    echo $e->getTraceAsString() . "\n";
}
