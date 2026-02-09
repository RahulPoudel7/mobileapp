<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Gift;
use App\Services\DistanceService;
use App\Exceptions\DistanceException;


class OrderController extends Controller
{
    protected DistanceService $distanceService;

    public function __construct(DistanceService $distanceService)
    {
        $this->distanceService = $distanceService;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient_name'    => ['required', 'string', 'max:255'],
            'recipient_phone'   => ['required', 'string', 'max:30'],
            'delivery_address'  => ['required', 'string', 'max:255'],
            'payment_method'    => ['required', 'string', 'in:esewa,cod'],
            'has_personal_note' => ['sometimes', 'boolean'],
            'has_gift_wrapping' => ['sometimes', 'boolean'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.gift_id'   => ['required', 'integer', 'exists:gifts,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
        ]);

        // Calculate subtotal
        $subtotal = 0.0;
        $items = $data['items'];

        foreach ($items as $item) {
            $gift = Gift::findOrFail($item['gift_id']);
            $subtotal += (float) $gift->price * (int) $item['quantity'];
        }

        // Validate subtotal
        if ($subtotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid order subtotal.',
            ], 422);
        }

        // Get distance (km) for the delivery address
        try {
            $distanceKm = $this->distanceService->getDistanceInKm($data['delivery_address']);
        } catch (DistanceException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to determine delivery distance. ' . $e->getMessage(),
            ], 422);
        }

        // Enforce maximum delivery distance
        if ($distanceKm > 50) {
            return response()->json([
                'success' => false,
                'message' => 'Contact support for delivery charge',
                'distance_km' => $distanceKm,
            ], 422);
        }

        // Calculate delivery charge
        $deliveryCharge = $this->calculateDeliveryCharge($subtotal, $distanceKm);
        $totalAmount = $subtotal + $deliveryCharge;

        // Wrap in transaction
        return DB::transaction(function () use ($data, $subtotal, $deliveryCharge, $totalAmount, $distanceKm, $items) {
            // Create the order
            $order = Order::create([
                'user_id'           => Auth::id(),
                'subtotal'          => $subtotal,
                'delivery_charge'   => $deliveryCharge,
                'total_amount'      => $totalAmount,
                'distance_km'       => $distanceKm,
                'payment_method'    => $data['payment_method'],
                'recipient_name'    => $data['recipient_name'],
                'recipient_phone'   => $data['recipient_phone'],
                'delivery_address'  => $data['delivery_address'],
                'status'            => ($data['payment_method'] === 'esewa') ? 'pending_payment' : 'pending',
                'has_personal_note' => $data['has_personal_note'] ?? false,
                'has_gift_wrapping' => $data['has_gift_wrapping'] ?? false,
            ]);

            // Create order items
            $totalQuantity = 0;
            foreach ($items as $item) {
                $gift = Gift::findOrFail($item['gift_id']);

                $order->items()->create([
                    'gift_id'  => $gift->id,
                    'quantity' => $item['quantity'],
                    'price'    => $gift->price,
                ]);

                $totalQuantity += $item['quantity'];
            }

            // Update quantity
            $order->update(['quantity' => $totalQuantity]);

            // Handle payment method
            if ($data['payment_method'] === 'esewa') {
                // For eSewa, return pending_payment with redirect URL placeholder
                return response()->json([
                    'success' => true,
                    'message' => 'Order created. Proceed to eSewa payment.',
                    'data'    => [
                        'order_id'        => $order->id,
                        'order_number'    => 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                        'status'          => $order->status,
                        'subtotal'        => (float) $order->subtotal,
                        'delivery_charge' => (float) $order->delivery_charge,
                        'total_amount'    => (float) $order->total_amount,
                        'distance_km'     => (float) $order->distance_km,
                        'payment_method'  => $order->payment_method,
                        'esewa_redirect_url' => route('api.esewa.redirect', ['order_id' => $order->id]),
                    ],
                ], 201);
            } else {
                // For COD, return order data with delivery charge
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully.',
                    'data'    => [
                        'order_id'        => $order->id,
                        'order_number'    => 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                        'status'          => $order->status,
                        'subtotal'        => (float) $order->subtotal,
                        'delivery_charge' => (float) $order->delivery_charge,
                        'total_amount'    => (float) $order->total_amount,
                        'distance_km'     => (float) $order->distance_km,
                        'payment_method'  => $order->payment_method,
                    ],
                ], 201);
            }
        });
    }


    public function myOrders(Request $request)
    {
        $orders = Order::with(['items.gift'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders fetched successfully.',
            'count'   => $orders->count(),
            'data'    => $orders->map(function ($order) {
                return [
                    'id'                => $order->id,
                    'order_number'      => 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'created_at'        => $order->created_at->toDateString(),
                    'status'            => $order->status,
                    'subtotal'          => (float) $order->subtotal,
                    'delivery_charge'   => (float) $order->delivery_charge,
                    'total_amount'      => (float) $order->total_amount,
                    'distance_km'       => (float) $order->distance_km,
                    'payment_method'    => $order->payment_method,
                    'items'             => $order->items->map(function ($item) {
                        return [
                            'quantity' => $item->quantity,
                            'price'    => (float) $item->price,
                            'gift'     => [
                                'id'        => $item->gift->id,
                                'name'      => $item->gift->name,
                                'image_url' => $item->gift->image_url,
                            ],
                        ];
                    }),
                    'recipient_name'   => $order->recipient_name,
                    'recipient_phone'  => $order->recipient_phone,
                    'delivery_address' => $order->delivery_address,
                ];
            }),
        ]);
    }

    /**
     * Calculate delivery charge based on distance and subtotal.
     *
     * Business rules:
     * - If subtotal >= 5000: free delivery (Rs. 0)
     * - 0–10 km: Rs. 80
     * - 10–20 km: Rs. 120
     * - 20–50 km: Rs. 160
     * - 50+ km: not allowed (validation done upstream)
     */
    private function calculateDeliveryCharge(float $subtotal, float $distanceKm): float
    {
        // Free delivery for orders over 5000
        if ($subtotal >= 5000) {
            return 0.0;
        }

        // Distance-based pricing
        if ($distanceKm <= 10) {
            return 80.0;
        } elseif ($distanceKm <= 20) {
            return 120.0;
        } else {
            // 20–50 km
            return 160.0;
        }
    }
}