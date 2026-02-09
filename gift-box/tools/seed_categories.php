<?php
chdir(__DIR__ . '/..');
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

$categories = [
    [
        'name' => 'Birthday',
        'slug' => 'birthday',
        'description' => 'Birthday gifts and celebration essentials.',
        'image_url' => null,
    ],
    [
        'name' => 'Anniversary',
        'slug' => 'anniversary',
        'description' => 'Anniversary gift ideas for couples and loved ones.',
        'image_url' => null,
    ],
    [
        'name' => 'Seasonal',
        'slug' => 'seasonal',
        'description' => 'Seasonal collections for festivals and special occasions.',
        'image_url' => null,
    ],
    [
        'name' => 'Corporate',
        'slug' => 'corporate',
        'description' => 'Corporate gifting for clients, teams, and partners.',
        'image_url' => null,
    ],
];

foreach ($categories as $category) {
    Category::updateOrCreate(
        ['name' => $category['name']],
        $category
    );
}

echo "ok";
