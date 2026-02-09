<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EsewaService
{
    protected $merchantId;
    protected $secretKey;
    protected $verifyUrl;

    public function __construct()
    {
        $this->merchantId = env('ESEWA_MERCHANT_ID');
        $this->secretKey = env('ESEWA_SECRET_KEY');
        $this->verifyUrl = env('ESEWA_VERIFY_URL');
    }

    public function verifyPayment($amount, $transactionUuid, $productCode)
    {
        // Calculate the signature required for verification
        // Format: total_amount,transaction_uuid,product_code
        $message = "total_amount={$amount},transaction_uuid={$transactionUuid},product_code={$productCode}";
        $signature = $this->generateSignature($message);

        // Make the GET request to eSewa to verify the transaction
        $response = Http::get($this->verifyUrl, [
            'product_code' => $productCode,
            'total_amount' => $amount,
            'transaction_uuid' => $transactionUuid,
            'signature' => $signature // Only required if eSewa V2 enforces signature on status check
        ]);

        if ($response->successful()) {
            $data = $response->json();
            // Check if status is COMPLETE
            if (isset($data['status']) && $data['status'] === 'COMPLETE') {
                return ['success' => true, 'data' => $data];
            }
        }

        return ['success' => false, 'message' => 'Verification failed', 'details' => $response->body()];
    }

    private function generateSignature($message)
    {
        $s = hash_hmac('sha256', $message, $this->secretKey, true);
        return base64_encode($s);
    }
}