<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $rows = DB::table('personal_access_tokens')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();

    echo "Last personal_access_tokens rows:\n";
    foreach ($rows as $row) {
        echo json_encode((array) $row, JSON_UNESCAPED_SLASHES) . "\n";
    }

    if ($rows->isEmpty()) {
        echo "(no rows found)\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
