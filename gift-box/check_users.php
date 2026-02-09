<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
$users = User::all(['id', 'name', 'email']);
echo "\n=== Existing Users ===\n";
foreach ($users as $user) {
    echo "- {$user->email} ({$user->name})\n";
}
echo "\n";
