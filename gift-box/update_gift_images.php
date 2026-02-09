<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Gift;

// Update gifts with image URLs
$gifts = Gift::all();

$imageUrls = [
    'Red Rose Bouquet' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?w=500&h=500&fit=crop',
    'Chocolate Deluxe Box' => 'https://images.unsplash.com/photo-1599599810694-b5ac4dd0b5c4?w=500&h=500&fit=crop',
    'Perfume Gift Set' => 'https://images.unsplash.com/photo-1585286915857-b04b78eda432?w=500&h=500&fit=crop',
    'Desk Organizer' => 'https://images.unsplash.com/photo-1593642632505-c4cb3cc2d1b9?w=500&h=500&fit=crop',
    'Gift Hamper' => 'https://images.unsplash.com/photo-1549465120-7786f6aa677d?w=500&h=500&fit=crop',
    'Greeting Card' => 'https://images.unsplash.com/photo-1589265556014-aaed1b88b334?w=500&h=500&fit=crop',
];

foreach ($gifts as $gift) {
    if (array_key_exists($gift->name, $imageUrls)) {
        $gift->update(['image' => $imageUrls[$gift->name]]);
        echo "✓ Updated: {$gift->name}\n";
    }
}

echo "\n✓ All gift images updated!\n\n";
