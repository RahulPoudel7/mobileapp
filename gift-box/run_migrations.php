<?php
// Change to project root
chdir(__DIR__);

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Get Artisan
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Run migrations
$status = $kernel->call('migrate', ['--force' => true]);

echo "Migration completed with status: " . $status . "\n";
