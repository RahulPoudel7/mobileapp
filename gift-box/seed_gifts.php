<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Gift;
use App\Models\Category;

// Disable foreign key checks temporarily (MySQL)
DB::statement('SET FOREIGN_KEY_CHECKS=0');

$categories = Category::orderBy('id')->get();
if ($categories->isEmpty()) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "\n✗ No categories found. Please seed categories first.\n\n";
    exit(1);
}

$categoryList = $categories->values();
$categoryByTag = [];
$tagKeywords = ['birthday', 'anniversary', 'seasonal', 'corporate'];
foreach ($categoryList as $category) {
    $key = strtolower((string) ($category->slug ?? $category->name));
    foreach ($tagKeywords as $tag) {
        if (strpos($key, $tag) !== false) {
            $categoryByTag[$tag] = $category;
        }
    }
}

$images = [
    'flowers' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?auto=format&fit=crop&w=600&q=80',
    'chocolate' => 'https://images.unsplash.com/photo-1599599810694-b5ac4dd0b5c4?auto=format&fit=crop&w=600&q=80',
    'cake' => 'https://images.unsplash.com/photo-1542826438-93f25c96f7a0?auto=format&fit=crop&w=600&q=80',
    'balloons' => 'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=600&q=80',
    'photo_frame' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=600&q=80',
    'candle' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=600&q=80',
    'plush' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=600&q=80',
    'confetti' => 'https://images.unsplash.com/photo-1464349153735-7db50ed83c84?auto=format&fit=crop&w=600&q=80',
    'cookies' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?auto=format&fit=crop&w=600&q=80',
    'perfume' => 'https://images.unsplash.com/photo-1585286915857-b04b78eda432?auto=format&fit=crop&w=600&q=80',
    'wine' => 'https://images.unsplash.com/photo-1510627498534-cf7e9002facc?auto=format&fit=crop&w=600&q=80',
    'dinner' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=600&q=80',
    'jewelry' => 'https://images.unsplash.com/photo-1519744792095-2f2205e87b6f?auto=format&fit=crop&w=600&q=80',
    'scrapbook' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=600&q=80',
    'spa' => 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=600&q=80',
    'movie' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80',
    'mugs' => 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?auto=format&fit=crop&w=600&q=80',
    'keepsake' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=600&q=80',
    'lights' => 'https://images.unsplash.com/photo-1482517967863-00e15c9b44be?auto=format&fit=crop&w=600&q=80',
    'cocoa' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=600&q=80',
    'scarf' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=600&q=80',
    'garden' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&w=600&q=80',
    'picnic' => 'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=600&q=80',
    'sparklers' => 'https://images.unsplash.com/photo-1464349153735-7db50ed83c84?auto=format&fit=crop&w=600&q=80',
    'fruit' => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?auto=format&fit=crop&w=600&q=80',
    'ornament' => 'https://images.unsplash.com/photo-1512389142860-9c449e58a543?auto=format&fit=crop&w=600&q=80',
    'pumpkin' => 'https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=600&q=80',
    'desk' => 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?auto=format&fit=crop&w=600&q=80',
    'notebook' => 'https://images.unsplash.com/photo-1519682577862-22b62b24e493?auto=format&fit=crop&w=600&q=80',
    'pens' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=600&q=80',
    'coffee' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80',
    'earbuds' => 'https://images.unsplash.com/photo-1518443895914-6b39f9f1e2f7?auto=format&fit=crop&w=600&q=80',
    'card_holder' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=600&q=80',
    'desk_plant' => 'https://images.unsplash.com/photo-1467413880896-92256a1d82f2?auto=format&fit=crop&w=600&q=80',
    'snack' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?auto=format&fit=crop&w=600&q=80',
    'award' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=600&q=80',
    'bottle' => 'https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=600&q=80',
    'baby_basket' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=600&q=80',
    'baby_blanket' => 'https://images.unsplash.com/photo-1504151932400-72d4384f04b3?auto=format&fit=crop&w=600&q=80',
    'baby_cards' => 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=600&q=80',
    'newborn' => 'https://images.unsplash.com/photo-1504151932400-72d4384f04b3?auto=format&fit=crop&w=600&q=80',
    'baby_frame' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=600&q=80',
    'oils' => 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=600&q=80',
    'tea' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=600&q=80',
    'yoga' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=600&q=80',
    'diffuser' => 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=600&q=80',
];

Gift::truncate();

$gifts = [
    ['name' => 'Red Rose Bouquet', 'description' => 'Fresh red roses arranged in a classic bouquet.', 'price' => 1499.00, 'category' => 'anniversary', 'image_key' => 'flowers', 'is_featured' => true],
    ['name' => 'Mixed Flower Bouquet', 'description' => 'Colorful seasonal blooms wrapped for gifting.', 'price' => 1299.00, 'category' => 'birthday', 'image_key' => 'flowers'],
    ['name' => 'Birthday Cake Box', 'description' => 'Assorted mini cakes in a gift box.', 'price' => 999.00, 'category' => 'birthday', 'image_key' => 'cake'],
    ['name' => 'Balloon Bouquet Set', 'description' => 'Helium balloon set for celebrations.', 'price' => 699.00, 'category' => 'birthday', 'image_key' => 'balloons'],
    ['name' => 'Chocolate Deluxe Box', 'description' => 'Premium assorted chocolates.', 'price' => 899.00, 'category' => 'birthday', 'image_key' => 'chocolate', 'is_featured' => true],
    ['name' => 'Personalized Photo Frame', 'description' => 'Elegant frame for a favorite memory.', 'price' => 799.00, 'category' => 'birthday', 'image_key' => 'photo_frame'],
    ['name' => 'Scented Candle Trio', 'description' => 'Three soothing scented candles.', 'price' => 1099.00, 'category' => 'birthday', 'image_key' => 'candle'],
    ['name' => 'Teddy Bear Plush', 'description' => 'Soft plush teddy bear.', 'price' => 599.00, 'category' => 'birthday', 'image_key' => 'plush'],
    ['name' => 'Celebration Confetti Kit', 'description' => 'Party confetti set for birthdays.', 'price' => 299.00, 'category' => 'birthday', 'image_key' => 'confetti'],
    ['name' => 'Gourmet Cookie Tin', 'description' => 'Fresh baked cookies in a tin.', 'price' => 699.00, 'category' => 'birthday', 'image_key' => 'cookies'],

    ['name' => 'Perfume Gift Set', 'description' => 'Luxury fragrance set for couples.', 'price' => 2499.00, 'category' => 'anniversary', 'image_key' => 'perfume', 'is_featured' => true],
    ['name' => 'Couples Wine Set', 'description' => 'Two-bottle wine gift set.', 'price' => 2799.00, 'category' => 'anniversary', 'image_key' => 'wine'],
    ['name' => 'Romantic Dinner Kit', 'description' => 'Dinner essentials for a cozy night.', 'price' => 1899.00, 'category' => 'anniversary', 'image_key' => 'dinner'],
    ['name' => 'Silver Necklace Gift', 'description' => 'Minimal silver necklace in a gift box.', 'price' => 2199.00, 'category' => 'anniversary', 'image_key' => 'jewelry'],
    ['name' => 'Memory Scrapbook Kit', 'description' => 'DIY scrapbook kit for memories.', 'price' => 1299.00, 'category' => 'anniversary', 'image_key' => 'scrapbook'],
    ['name' => 'Luxury Bath Set', 'description' => 'Premium bath salts and lotions.', 'price' => 1599.00, 'category' => 'anniversary', 'image_key' => 'spa'],
    ['name' => 'Date Night Movie Box', 'description' => 'Popcorn and snacks for movie night.', 'price' => 999.00, 'category' => 'anniversary', 'image_key' => 'movie'],
    ['name' => 'Roses and Chocolate Combo', 'description' => 'Bouquet paired with chocolates.', 'price' => 1999.00, 'category' => 'anniversary', 'image_key' => 'flowers'],
    ['name' => 'His and Hers Mug Set', 'description' => 'Matching mugs for couples.', 'price' => 799.00, 'category' => 'anniversary', 'image_key' => 'mugs'],
    ['name' => 'Anniversary Keepsake Box', 'description' => 'Wooden box for keepsakes.', 'price' => 1399.00, 'category' => 'anniversary', 'image_key' => 'keepsake'],

    ['name' => 'Festive Lights Garland', 'description' => 'Warm lights for seasonal decor.', 'price' => 899.00, 'category' => 'seasonal', 'image_key' => 'lights'],
    ['name' => 'Holiday Hot Cocoa Kit', 'description' => 'Cocoa mix with marshmallows.', 'price' => 749.00, 'category' => 'seasonal', 'image_key' => 'cocoa'],
    ['name' => 'Winter Knit Scarf', 'description' => 'Cozy knit scarf for winter.', 'price' => 1099.00, 'category' => 'seasonal', 'image_key' => 'scarf'],
    ['name' => 'Spring Garden Starter', 'description' => 'Starter kit for home gardening.', 'price' => 1199.00, 'category' => 'seasonal', 'image_key' => 'garden'],
    ['name' => 'Autumn Scented Candle', 'description' => 'Warm autumn fragrance candle.', 'price' => 599.00, 'category' => 'seasonal', 'image_key' => 'candle'],
    ['name' => 'Summer Picnic Basket', 'description' => 'Ready-to-go picnic basket.', 'price' => 1899.00, 'category' => 'seasonal', 'image_key' => 'picnic'],
    ['name' => 'New Year Sparkler Pack', 'description' => 'Party sparklers for celebrations.', 'price' => 399.00, 'category' => 'seasonal', 'image_key' => 'sparklers'],
    ['name' => 'Seasonal Fruit Hamper', 'description' => 'Assorted seasonal fruits.', 'price' => 1299.00, 'category' => 'seasonal', 'image_key' => 'fruit'],
    ['name' => 'Festive Ornament Set', 'description' => 'Holiday ornament set.', 'price' => 899.00, 'category' => 'seasonal', 'image_key' => 'ornament'],
    ['name' => 'Pumpkin Spice Gift Box', 'description' => 'Cozy fall-themed gift box.', 'price' => 1099.00, 'category' => 'seasonal', 'image_key' => 'pumpkin'],

    ['name' => 'Executive Desk Organizer', 'description' => 'Wooden organizer for office desks.', 'price' => 1599.00, 'category' => 'corporate', 'image_key' => 'desk', 'is_featured' => true],
    ['name' => 'Premium Notebook Set', 'description' => 'Two premium notebooks.', 'price' => 799.00, 'category' => 'corporate', 'image_key' => 'notebook'],
    ['name' => 'Branded Pen Set', 'description' => 'Elegant pen set for professionals.', 'price' => 699.00, 'category' => 'corporate', 'image_key' => 'pens'],
    ['name' => 'Coffee Lover Kit', 'description' => 'Coffee beans and brewing tools.', 'price' => 1199.00, 'category' => 'corporate', 'image_key' => 'coffee'],
    ['name' => 'Wireless Earbuds Gift', 'description' => 'Compact wireless earbuds.', 'price' => 2499.00, 'category' => 'corporate', 'image_key' => 'earbuds'],
    ['name' => 'Business Card Holder', 'description' => 'Leather card holder.', 'price' => 599.00, 'category' => 'corporate', 'image_key' => 'card_holder'],
    ['name' => 'Desk Plant Kit', 'description' => 'Small desk plant with pot.', 'price' => 699.00, 'category' => 'corporate', 'image_key' => 'desk_plant'],
    ['name' => 'Team Snack Box', 'description' => 'Snack assortment for teams.', 'price' => 1399.00, 'category' => 'corporate', 'image_key' => 'snack'],
    ['name' => 'Appreciation Plaque', 'description' => 'Simple award plaque.', 'price' => 1299.00, 'category' => 'corporate', 'image_key' => 'award'],
    ['name' => 'Custom Water Bottle', 'description' => 'Reusable bottle for daily use.', 'price' => 499.00, 'category' => 'corporate', 'image_key' => 'bottle'],

    ['name' => 'Baby Welcome Basket', 'description' => 'Basket with newborn essentials.', 'price' => 1999.00, 'category' => 'birthday', 'image_key' => 'baby_basket', 'is_featured' => true],
    ['name' => 'Soft Baby Blanket', 'description' => 'Cozy blanket for newborns.', 'price' => 899.00, 'category' => 'birthday', 'image_key' => 'baby_blanket'],
    ['name' => 'Baby Milestone Cards', 'description' => 'Milestone cards for photos.', 'price' => 499.00, 'category' => 'birthday', 'image_key' => 'baby_cards'],
    ['name' => 'Newborn Care Kit', 'description' => 'Gentle care kit for babies.', 'price' => 1299.00, 'category' => 'birthday', 'image_key' => 'newborn'],
    ['name' => 'Baby Keepsake Frame', 'description' => 'Frame for baby handprints.', 'price' => 1099.00, 'category' => 'birthday', 'image_key' => 'baby_frame'],

    ['name' => 'Spa Relaxation Kit', 'description' => 'Relaxing spa essentials.', 'price' => 1699.00, 'category' => 'seasonal', 'image_key' => 'spa', 'is_featured' => true],
    ['name' => 'Essential Oil Set', 'description' => 'Set of calming essential oils.', 'price' => 1299.00, 'category' => 'seasonal', 'image_key' => 'oils'],
    ['name' => 'Herbal Tea Collection', 'description' => 'Assorted herbal teas.', 'price' => 799.00, 'category' => 'seasonal', 'image_key' => 'tea'],
    ['name' => 'Yoga Starter Set', 'description' => 'Yoga mat and stretch band.', 'price' => 1799.00, 'category' => 'seasonal', 'image_key' => 'yoga'],
    ['name' => 'Aromatherapy Diffuser', 'description' => 'Ultrasonic diffuser for aromas.', 'price' => 1899.00, 'category' => 'seasonal', 'image_key' => 'diffuser'],
];

$fallbackIndex = 0;
foreach ($gifts as $gift) {
    $category = $categoryByTag[$gift['category']] ?? $categoryList[$fallbackIndex % $categoryList->count()];
    $fallbackIndex++;
    Gift::create([
        'name' => $gift['name'],
        'description' => $gift['description'],
        'price' => $gift['price'],
        'image_url' => $images[$gift['image_key']],
        'category_id' => $category->id,
        'is_active' => true,
        'is_featured' => $gift['is_featured'] ?? false,
    ]);
}

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n✓ Created " . count($gifts) . " gifts!\n\n";
