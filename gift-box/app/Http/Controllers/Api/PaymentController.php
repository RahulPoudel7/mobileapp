<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    /**
     * Handle successful eSewa payment
     */
    public function esewaSuccess(Request $request)
    {
        // Log callback for debugging
        \Log::info('eSewa Success Callback Received', $request->all());
        
        // eSewa sends data in base64 encoded 'data' parameter
        $data = $request->get('data');
        
        if ($data) {
            // Decode base64 and parse JSON
            $decoded = base64_decode($data);
            $esewaData = json_decode($decoded, true);
            
            $transactionUuid = $esewaData['transaction_uuid'] ?? null;
            $totalAmount = $esewaData['total_amount'] ?? null;
            $status = $esewaData['status'] ?? null;
        } else {
            // Fallback to direct parameters (old format)
            $transactionUuid = $request->get('transaction_uuid');
            $totalAmount = $request->get('total_amount');
            $status = $request->get('status');
        }
        
        if (!$transactionUuid) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction UUID missing'
            ], 400);
        }
        
        // Find order by transaction UUID
        $order = Order::where('transaction_uuid', $transactionUuid)->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        // Verify amount matches - use lenient comparison for floating point
        $dbAmount = floatval($order->total_amount);
        $esewaAmount = floatval($totalAmount);
        
        // Allow small floating point variations (within 1 unit)
        if (abs($dbAmount - $esewaAmount) > 1.0) {
            \Log::warning('Amount mismatch', [
                'order_amount' => $dbAmount,
                'esewa_amount' => $esewaAmount,
                'difference' => abs($dbAmount - $esewaAmount)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Amount mismatch'
            ], 400);
        }
        
        // Check if payment status is complete
        if ($status === 'COMPLETE') {
            // Update order payment status to paid
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed' // Auto-confirm order on successful eSewa payment
            ]);
            
            \Log::info('Payment marked as paid', ['order_id' => $order->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ], 200);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Payment incomplete'
        ], 400);
    }
    
    /**
     * Handle failed eSewa payment
     */
    public function esewaFailure(Request $request)
    {
        // Log callback for debugging
        \Log::info('eSewa Failure Callback Received', $request->all());
        
        // eSewa sends details on failure too
        $transactionUuid = $request->get('transaction_uuid');
        $failureMessage = $request->get('error') ?? 'Payment failed';
        
        // Find order by transaction UUID
        $order = Order::where('transaction_uuid', $transactionUuid)->first();
        
        if ($order) {
            // Keep order as pending but mark payment as failed
            $order->update([
                'payment_status' => 'failed'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Payment failed: ' . $failureMessage
        ], 400);
    }
}
