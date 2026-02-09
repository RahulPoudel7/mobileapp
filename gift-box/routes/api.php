<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\GiftApiController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/users', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/forgot-password', [AuthController::class, 'requestPasswordReset']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Debug endpoint - remove in production
Route::get('/test-otp/{email}', function ($email) {
    $otp = \App\Models\Otp::where('email', $email)->latest()->first();
    if ($otp && $otp->isValid()) {
        return response()->json([
            'success' => true,
            'message' => 'OTP found',
            'data' => [
                'email' => $otp->email,
                'otp' => $otp->otp,
                'expires_at' => $otp->expires_at,
            ]
        ]);
    }
    return response()->json([
        'success' => false,
        'message' => 'No valid OTP found'
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    // auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/account/delete', [AuthController::class, 'deleteAccount']);

    // user endpoints
    Route::post('/user/default-address', [AuthController::class, 'updateDefaultAddress']);

    // notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'deleteAll']);

    // gifts
    Route::get('/gifts', [GiftApiController::class, 'index']);
    Route::get('/gifts-featured', [GiftApiController::class, 'featured']);
    Route::get('/gifts/{id}', [GiftApiController::class, 'show']);
    Route::get('/gifts-search', [GiftApiController::class, 'search']);
    Route::get('/gifts-by-category/{categoryId}', [GiftApiController::class, 'getByCategory']);

    // orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/orders/{id}/status', [OrderController::class, 'getStatus']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{id}/set-delivery-date', [OrderController::class, 'setDeliveryDate']);
    Route::post('/calculate-delivery', [OrderController::class, 'calculateDeliveryPreview']);
    
    // Admin: Update order status
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateOrderStatus']);
    
    // Payment verification (needs auth for user context)
    Route::post('/payment/verify', [PaymentApiController::class, 'verifyEsewa']);
});

// eSewa payment callbacks (no auth required - called by eSewa server)
Route::get('/payment/esewa/success', [PaymentController::class, 'esewaSuccess']);
Route::get('/payment/esewa/failure', [PaymentController::class, 'esewaFailure']);
