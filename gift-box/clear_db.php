<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Otp;

// Disable foreign key constraints temporarily
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Delete all users and OTPs
User::truncate();
Otp::truncate();

// Re-enable foreign key constraints
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n✓ Database cleared! All users and OTPs deleted.\n\n";
