<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PaymentApiController extends Controller
{
    public function verifyEsewa(Request $request)
    {
        try {
            // 1. Validate the input from your Android App
            $validated = $request->validate([
                'order_id' => 'required|integer',
                'amount'   => 'required|numeric',
                'refId'    => 'required|string', // The transaction_uuid passed back from mobile
            ]);

            \Log::info('Payment Verify Request', $validated);

            // 2. Find the Order
            $order = Order::find($request->order_id);
            
            if (!$order) {
                \Log::warning('Order not found', ['order_id' => $request->order_id]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Order not found'
                ], 404);
            }

            // 3. Security Check: Validate Amount
            // Use more lenient comparison for floating point values
            $dbAmount = floatval($order->total_amount);
            $requestAmount = floatval($request->amount);
            
            // Allow small floating point variations (within 1 unit)
            if (abs($dbAmount - $requestAmount) > 1.0) {
                \Log::warning('Amount mismatch', [
                    'order_amount' => $dbAmount,
                    'request_amount' => $requestAmount,
                    'difference' => abs($dbAmount - $requestAmount)
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Amount mismatch. verification failed.'
                ], 400);
            }

            // 4. TEST MODE: Skip eSewa verification for testing
            // In production, you should verify with eSewa API
            $testMode = env('APP_ENV') !== 'production'; // or use a specific TEST_MODE flag
            
            if ($testMode) {
                // For testing: directly mark as paid without eSewa verification
                $order->update([
                    'status'           => 'confirmed',
                    'payment_status'   => 'paid',
                ]);

                \Log::info('Test Mode Payment Verification - Order Marked Paid', [
                    'order_id' => $order->id,
                    'amount' => $request->amount,
                    'refId' => $request->refId
                ]);

                return response()->json([
                    'success' => true, 
                    'message' => 'Payment Verified Successfully (Test Mode)',
                    'order_id' => $order->id,
                    'payment_status' => 'paid'
                ]);
            }

            // 5. PRODUCTION MODE: Verify with eSewa (Server-to-Server)
            // We use the data SAVED in our database to ensure it matches what we sent originally
            $verifyUrl = env('ESEWA_VERIFY_URL', 'https://rc-epay.esewa.com.np/api/epay/transaction/status');        
            $response = Http::get($verifyUrl, [
                'product_code'     => 'EPAYTEST',
                'total_amount'     => (string)$order->total_amount, // eSewa is picky about types
                'transaction_uuid' => $order->transaction_uuid,    // Use the stored UUID
            ]);

            // 6. Handle eSewa Response
            if ($response->successful()) {
                $esewaData = $response->json();

                // Check if status is COMPLETE
                if (isset($esewaData['status']) && $esewaData['status'] === 'COMPLETE') {
                    
                    // Success! Update your database
                    $order->update([
                        'status'           => 'confirmed',
                        'payment_status'   => 'paid',
                    ]);

                    return response()->json([
                        'success' => true, 
                        'message' => 'Payment Verified Successfully',
                        'order_id' => $order->id,
                        'payment_status' => 'paid'
                    ]);
                }
            }

            // 7. Fail Case
            return response()->json([
                'success' => false, 
                'message' => 'eSewa Verification Failed. Transaction not found or pending.',
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Payment Verification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification error: ' . $e->getMessage()
            ], 500);
        }
    }
}