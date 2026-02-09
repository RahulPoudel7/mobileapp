<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\DistanceService;
use App\Exceptions\DistanceException;
use Illuminate\Support\Str; // <--- IMPORTANT: Needed for UUID generation
use App\Models\Notification;

// Define fee constants
define('GIFT_WRAPPING_FEE', 100.00); 
define('PERSONAL_NOTE_FEE', 100.00); 

class OrderController extends Controller
{
    protected DistanceService $distanceService;

    public function __construct(DistanceService $distanceService)
    {
        $this->distanceService = $distanceService;
    }

    /**
     * Store a new order and generate payment details
     */
    public function store(Request $request)
    {
        // 1. VALIDATION
        $data = $request->validate([
            'recipient_name'    => ['required', 'string', 'max:255'],
            'recipient_phone'   => ['required', 'string', 'max:30'],
            'delivery_address'  => ['required', 'string', 'max:255'],
            
            // Coordinates required for distance calculation
            'delivery_lat'      => ['required', 'numeric'], 
            'delivery_lng'      => ['required', 'numeric'],

            'payment_method'    => ['required', 'string', 'in:esewa,cod'],
            'has_personal_note' => ['sometimes', 'boolean'],
            'personal_note_text'=> ['required_if:has_personal_note,true', 'nullable', 'string', 'max:500'],
            'has_gift_wrapping' => ['sometimes', 'boolean'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.gift_id'   => ['required', 'integer', 'exists:gifts,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
        ]);

        // 2. VALIDATE NEPAL LOCATION
        $lat = $data['delivery_lat'];
        $lng = $data['delivery_lng'];
        
        // Nepal boundaries: Lat 26.38 to 30.45, Lng 80.08 to 88.12
        if ($lat < 26.38 || $lat > 30.45 || $lng < 80.08 || $lng > 88.12) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery location must be within Nepal'
            ], 422);
        }

        // 2. CALCULATE SUBTOTAL
        $subtotal = 0.0;
        $items = $data['items'];

        foreach ($items as $item) {
            $gift = Gift::findOrFail($item['gift_id']);
            $lineTotal = (float) $gift->price * (int) $item['quantity'];
            $subtotal += $lineTotal;
        }

        if ($subtotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid subtotal'], 422);
        }

        // 3. CALCULATE FEES
        $personalNoteFee = ($data['has_personal_note'] ?? false) ? PERSONAL_NOTE_FEE : 0.0;
        $giftWrappingFee = ($data['has_gift_wrapping'] ?? false) ? GIFT_WRAPPING_FEE : 0.0;

        // 4. CALCULATE DISTANCE
        try {
            $distanceKm = $this->distanceService->getDistanceInKm(
                $data['delivery_lat'],
                $data['delivery_lng']
            );
        } catch (DistanceException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Unable to determine delivery distance. ' . $e->getMessage()
            ], 422);
        }

        // 5. VALIDATE MAX DISTANCE (Uncomment if needed)
        /*
        if ($distanceKm > 50) {
            return response()->json([
                'success' => false, 
                'message' => 'Delivery location is too far (over 50km).',
                'distance_km' => $distanceKm 
            ], 422);
        }
        */

        // 6. CALCULATE TOTALS
        $deliveryCharge = $this->calculateDeliveryCharge($subtotal, $distanceKm);
        $totalAmount = $subtotal + $deliveryCharge + $personalNoteFee + $giftWrappingFee;

        // 7. CREATE ORDER IN DATABASE (Pending Status)
        $order = DB::transaction(function () use ($data, $subtotal, $deliveryCharge, $totalAmount, $distanceKm, $items, $personalNoteFee, $giftWrappingFee) {
            
            // Generate Unique Transaction UUID
            // Format: Timestamp-UserID-RandomString
            $transactionUuid = time() . '-' . Auth::id() . '-' . Str::random(4);

            // Determine status
            // Keep all new orders as 'pending' initially for both COD and eSewa.
            // Admin / background processes can later move them through the lifecycle.
            $status = 'pending';

            $order = Order::create([
                'user_id'           => Auth::id(),
                'transaction_uuid'  => $transactionUuid,
                'subtotal'          => $subtotal,
                'personal_note_fee' => $personalNoteFee,
                'gift_wrapping_fee' => $giftWrappingFee,
                'delivery_charge'   => $deliveryCharge,
                'total_amount'      => $totalAmount,
                'distance_km'       => $distanceKm,
                'payment_method'    => $data['payment_method'],
                'recipient_name'    => $data['recipient_name'],
                'recipient_phone'   => $data['recipient_phone'],
                'delivery_address'  => $data['delivery_address'],
                'delivery_lat'      => $data['delivery_lat'],
                'delivery_lng'      => $data['delivery_lng'],
                'status'            => $status,
                'payment_status'    => 'unpaid',
                'has_personal_note' => $data['has_personal_note'] ?? false,
                'personal_note_text'=> ($data['has_personal_note'] ?? false) ? $data['personal_note_text'] : null,
                'has_gift_wrapping' => $data['has_gift_wrapping'] ?? false,
                'quantity'          => 0 // Will update below
            ]);

            // Create Order Items
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

            // Update total quantity
            $order->update(['quantity' => $totalQuantity]);

            return $order;
        });

        // ---------------------------------------------------------
        // CASE A: ESEWA PAYMENT (Return Signature + Test Payment URL)
        // ---------------------------------------------------------
        if ($data['payment_method'] === 'esewa') {
            
            $merchantCode = 'EPAYTEST'; // Use 'EPAYTEST' for testing
            $secretKey = '8gBm/:&EnhH.1/q'; // Test Secret Key

            // eSewa RC (test) base URL for payment (EPAY V2)
            $esewaBaseUrl = 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';

            // eSewa requires: amount + tax_amount + service_charge = total_amount
            // For now, we'll use total_amount as amount and 0 for tax
            $totalAmountFormatted = number_format($order->total_amount, 2, '.', '');
            $amount = $totalAmountFormatted;
            $taxAmount = '0';
            $serviceCharge = '0';

            // Generate signature AFTER formatting the total_amount
            // Signature String Format: "total_amount,transaction_uuid,product_code"
            $message = "total_amount={$totalAmountFormatted},transaction_uuid={$order->transaction_uuid},product_code={$merchantCode}";
            $signature = base64_encode(hash_hmac('sha256', $message, $secretKey, true));

            // Build params and encode as query string for a direct payment URL
            $esewaParams = [
                'amount'            => $amount,
                'tax_amount'        => $taxAmount,
                'total_amount'      => $totalAmountFormatted,
                'transaction_uuid'  => $order->transaction_uuid,
                'product_code'      => $merchantCode,
                'product_service_charge' => $serviceCharge,
                'product_delivery_charge' => '0',
                'success_url'       => url('/api/payment/esewa/success'),
                'failure_url'       => url('/api/payment/esewa/failure'),
                'signed_field_names' => 'total_amount,transaction_uuid,product_code',
                'signature'         => $signature,
            ];

            $paymentUrl = $esewaBaseUrl . '?' . http_build_query($esewaParams);

            return response()->json([
                'success' => true,
                'message' => 'Order created. Use the returned eSewa test URL or params to complete payment.',
                'data' => [
                    'order_id'         => $order->id,
                    'total_amount'     => (string)$order->total_amount,
                    'transaction_uuid' => $order->transaction_uuid,
                    'product_code'     => $merchantCode,
                    'signature'        => $signature,
                    'status'           => 'pending',
                    'esewa_payment_url'=> $paymentUrl,
                    'esewa_params'     => $esewaParams,
                ]
            ]);
        }

        // ---------------------------------------------------------
        // CASE B: CASH ON DELIVERY
        // ---------------------------------------------------------
        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully.',
            'data'    => [
                'order_id'     => $order->id,
                'order_number' => 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'total_amount' => (float) $order->total_amount,
                'status'       => $order->status,
            ],
        ], 201);
    }

    // ... [Keep your existing myOrders, show, getStatus, cancel, calculateDeliveryCharge, formatOrderData methods below as they were] ...
    
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
                return $this->formatOrderData($order);
            }),
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['items.gift'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatOrderData($order),
        ]);
    }

    public function getStatus($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'order_id'     => $order->id,
                'order_number' => 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'status'       => $order->status,
                'updated_at'   => $order->updated_at->toDateTimeString(),
            ],
        ]);
    }

    public function cancel($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled.',
            ], 422);
        }

        $oldStatus = $order->status;
        $order->update(['status' => 'cancelled']);

        // Create notification for cancellation
        $this->createOrderStatusNotification($order, $oldStatus, 'cancelled');

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data' => [
                'order_id' => $order->id,
                'status'   => $order->status,
            ],
        ]);
    }

    private function calculateDeliveryCharge(float $subtotal, float $distanceKm): float
    {
        // Free delivery for orders above NPR 5000
        if ($subtotal >= 5000) {
            return 0.0;
        }
        
        // Distance-based delivery charges (Nepal standard rates)
        if ($distanceKm <= 3) {
            return 60.0;   // Within 3km: NPR 60
        } elseif ($distanceKm <= 7) {
            return 100.0;  // 3-7km: NPR 100
        } elseif ($distanceKm <= 15) {
            return 150.0;  // 7-15km: NPR 150
        } elseif ($distanceKm <= 25) {
            return 200.0;  // 15-25km: NPR 200
        } elseif ($distanceKm <= 40) {
            return 300.0;  // 25-40km: NPR 300
        } else {
            return 500.0;  // 40+ km: NPR 500
        }
    }

    /**
     * Calculate delivery charge preview before order placement
     */
    public function calculateDeliveryPreview(Request $request)
    {
        $data = $request->validate([
            'delivery_lat' => ['required', 'numeric'],
            'delivery_lng' => ['required', 'numeric'],
            'subtotal'     => ['required', 'numeric', 'min:0'],
        ]);

        // Validate Nepal location
        $lat = $data['delivery_lat'];
        $lng = $data['delivery_lng'];
        
        // Nepal boundaries: Lat 26.38 to 30.45, Lng 80.08 to 88.12
        if ($lat < 26.38 || $lat > 30.45 || $lng < 80.08 || $lng > 88.12) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery location must be within Nepal'
            ], 422);
        }

        try {
            $distanceKm = $this->distanceService->getDistanceInKm(
                $data['delivery_lat'],
                $data['delivery_lng']
            );
        } catch (DistanceException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate distance: ' . $e->getMessage()
            ], 422);
        }

        $deliveryCharge = $this->calculateDeliveryCharge($data['subtotal'], $distanceKm);

        return response()->json([
            'success' => true,
            'data' => [
                'distance_km' => round($distanceKm, 2),
                'delivery_charge' => $deliveryCharge,
                'subtotal' => $data['subtotal'],
                'is_free_delivery' => $deliveryCharge == 0.0,
            ]
        ]);
    }

    private function formatOrderData($order)
    {
        return [
            'id'                => $order->id,
            'order_number'      => 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            'created_at'        => $order->created_at->toDateString(),
            'status'            => $order->status,
            'payment_status'    => $order->payment_status,
            'subtotal'          => (float) $order->subtotal,
            'personal_note_fee' => (float) $order->personal_note_fee,
            'gift_wrapping_fee' => (float) $order->gift_wrapping_fee,
            'delivery_charge'   => (float) $order->delivery_charge,
            'total_amount'      => (float) $order->total_amount,
            'distance_km'       => (float) $order->distance_km,
            'payment_method'    => $order->payment_method,
            'recipient_name'    => $order->recipient_name,
            'recipient_phone'   => $order->recipient_phone,
            'delivery_address'  => $order->delivery_address,
            'has_personal_note' => (bool) $order->has_personal_note,
            'personal_note_text'=> $order->personal_note_text,
            'has_gift_wrapping' => (bool) $order->has_gift_wrapping,
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
        ];
    }

    /**
     * Set delivery date for an order
     */
    public function setDeliveryDate($id, Request $request)
    {
        $data = $request->validate([
            'delivery_date' => ['required', 'date', 'date_format:d/m/Y'],
        ]);

        $order = Order::findOrFail($id);

        // Check if user owns this order
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Parse the date from dd/MM/yyyy format to Y-m-d
        $deliveryDate = \Carbon\Carbon::createFromFormat('d/m/Y', $data['delivery_date'])->format('Y-m-d');

        $order->update(['delivery_date' => $deliveryDate]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery date set successfully',
            'order' => [
                'id' => $order->id,
                'delivery_date' => $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') : null,
            ]
        ]);
    }

    /**
     * Update order status (Admin/System use)
     * Automatically creates notifications for status changes
     */
    public function updateOrderStatus($id, Request $request)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,accepted,processing,shipped,delivered,cancelled'],
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $data['status'];

        // Update status
        $order->update(['status' => $newStatus]);

        // Update delivered_at timestamp if status is delivered
        if ($newStatus === 'delivered' && !$order->delivered_at) {
            $order->update(['delivered_at' => now()]);
        }

        // Create notification for user
        $this->createOrderStatusNotification($order, $oldStatus, $newStatus);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'delivered_at' => $order->delivered_at ? $order->delivered_at->toDateTimeString() : null,
            ]
        ]);
    }

    /**
     * Create notification for order status change
     */
    private function createOrderStatusNotification($order, $oldStatus, $newStatus)
    {
        $orderNumber = 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
        
        // Define notification messages based on status
        $notifications = [
            'accepted' => [
                'title' => 'Order Confirmed',
                'message' => "Your order {$orderNumber} has been confirmed and is being prepared.",
            ],
            'processing' => [
                'title' => 'Order Processing',
                'message' => "Your order {$orderNumber} is now being processed.",
            ],
            'shipped' => [
                'title' => 'Order Shipped',
                'message' => "Great news! Your order {$orderNumber} has been shipped and is on its way.",
            ],
            'delivered' => [
                'title' => 'Order Delivered',
                'message' => "Your order {$orderNumber} has been successfully delivered. Thank you for your purchase!",
            ],
            'cancelled' => [
                'title' => 'Order Cancelled',
                'message' => "Your order {$orderNumber} has been cancelled.",
            ],
        ];

        // Only create notification if status has a notification message
        if (isset($notifications[$newStatus]) && $oldStatus !== $newStatus) {
            Notification::createForUser(
                $order->user_id,
                $notifications[$newStatus]['title'],
                $notifications[$newStatus]['message'],
                'order',
                ['order_id' => $order->id, 'order_number' => $orderNumber]
            );
        }
    }
}