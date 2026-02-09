<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gift;

class GiftApiController extends Controller
{
   public function index()
{
    $gifts = Gift::with('category')->get();   // 1) define it

    return response()->json([
        'success' => true,
        'message' => 'Gifts fetched successfully.',
        'count'   => $gifts->count(),
        'data'    => $gifts->map(function ($gift) {
            return [
                'id'          => $gift->id,
                'name'        => $gift->name,
                'description' => $gift->description,
                'price'       => (float) $gift->price,
                'image_url'   => $gift->image_url,
                'is_active'   => (bool) $gift->is_active,
                'category'    => [
                    'id'   => $gift->category?->id,
                    'name' => $gift->category?->name,
                ],
            ];
        }),
    ]);
}

public function featured()
{
    $gifts = Gift::with('category')
        ->where('is_featured', true)
        ->where('is_active', true)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Featured gifts fetched successfully.',
        'count'   => $gifts->count(),
        'data'    => $gifts->map(function ($gift) {
            return [
                'id'          => $gift->id,
                'name'        => $gift->name,
                'description' => $gift->description,
                'price'       => (float) $gift->price,
                'image_url'   => $gift->image_url,
                'is_active'   => (bool) $gift->is_active,
                'is_featured' => (bool) $gift->is_featured,
                'category'    => [
                    'id'   => $gift->category?->id,
                    'name' => $gift->category?->name,
                ],
            ];
        }),
    ]);
}

public function search()
{
    $query = request()->get('q', '');

    if (empty($query)) {
        return response()->json([
            'success' => false,
            'message' => 'Search query is required.',
            'data'    => [],
        ], 400);
    }

    $gifts = Gift::with('category')
        ->where('name', 'like', "%{$query}%")
        ->orWhere('description', 'like', "%{$query}%")
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Gifts search completed.',
        'count'   => $gifts->count(),
        'data'    => $gifts->map(function ($gift) {
            return [
                'id'          => $gift->id,
                'name'        => $gift->name,
                'description' => $gift->description,
                'price'       => (float) $gift->price,
                'image_url'   => $gift->image_url,
                'is_active'   => (bool) $gift->is_active,
                'category'    => [
                    'id'   => $gift->category?->id,
                    'name' => $gift->category?->name,
                ],
            ];
        }),
    ]);
}

public function getByCategory($categoryId)
{
    $gifts = Gift::with('category')
        ->where('category_id', $categoryId)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Gifts fetched successfully.',
        'count'   => $gifts->count(),
        'data'    => $gifts->map(function ($gift) {
            return [
                'id'          => $gift->id,
                'name'        => $gift->name,
                'description' => $gift->description,
                'price'       => (float) $gift->price,
                'image_url'   => $gift->image_url,
                'is_active'   => (bool) $gift->is_active,
                'category'    => [
                    'id'   => $gift->category?->id,
                    'name' => $gift->category?->name,
                ],
            ];
        }),
    ]);
}
}

