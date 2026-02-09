package com.example.giftbox.api;

import com.example.giftbox.models.LoginRequest;
import com.example.giftbox.models.LoginResponse;
import com.example.giftbox.models.RegisterRequest;
import com.example.giftbox.models.RegisterResponse;
import com.example.giftbox.models.ResendOtpRequest;
import com.example.giftbox.models.ResendOtpResponse;
import com.example.giftbox.models.VerifyOtpRequest;
import com.example.giftbox.models.VerifyOtpResponse;
import com.example.giftbox.models.GiftListResponse;
import com.example.giftbox.models.GiftDetailResponse;
import com.example.giftbox.models.OrderRequest;
import com.example.giftbox.models.OrderResponse;
import com.example.giftbox.models.OrderListResponse;
import com.example.giftbox.models.OrderDetailResponse;

import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.GET;
import retrofit2.http.Header;
import retrofit2.http.POST;
import retrofit2.http.Path;

public interface ApiService {
    // Auth endpoints
    @POST("api/users")
    Call<RegisterResponse> register(@Body RegisterRequest request);

    @POST("api/login")
    Call<LoginResponse> login(@Body LoginRequest request);

    @POST("api/verify-otp")
    Call<VerifyOtpResponse> verifyOtp(@Body VerifyOtpRequest request);

    @POST("api/resend-otp")
    Call<ResendOtpResponse> resendOtp(@Body ResendOtpRequest request);

    @POST("api/forgot-password")
    Call<com.google.gson.JsonObject> requestPasswordReset(@Body com.google.gson.JsonObject emailBody);

    @POST("api/reset-password")
    Call<com.google.gson.JsonObject> resetPassword(@Body com.google.gson.JsonObject resetBody);

    @POST("api/logout")
    Call<Void> logout(@Header("Authorization") String token);

    @GET("api/profile")
    Call<com.google.gson.JsonObject> getProfile(@Header("Authorization") String token);

    @POST("api/change-password")
    Call<com.google.gson.JsonObject> changePassword(@Header("Authorization") String token, @Body com.google.gson.JsonObject passwordBody);

    // Gift endpoints
    @GET("api/gifts")
    Call<GiftListResponse> getGifts(@Header("Authorization") String token);

    @GET("api/gifts-featured")
    Call<GiftListResponse> getFeaturedGifts(@Header("Authorization") String token);

    @GET("api/gifts-search")
    Call<GiftListResponse> searchGifts(@Header("Authorization") String token, @retrofit2.http.Query("q") String query);

    @GET("api/gifts-by-category/{categoryId}")
    Call<GiftListResponse> getGiftsByCategory(@Header("Authorization") String token, @retrofit2.http.Path("categoryId") int categoryId);

    @GET("api/gifts/{id}")
    Call<GiftDetailResponse> getGiftDetail(@Header("Authorization") String token);

    // Order endpoints
    @POST("api/orders")
    Call<OrderResponse> createOrder(@Header("Authorization") String token, @Body OrderRequest request);

    @POST("api/orders")
    Call<com.google.gson.JsonObject> createOrder(@Header("Authorization") String token, @Body com.google.gson.JsonObject orderBody);

    @POST("api/orders/{id}/set-delivery-date")
    Call<com.google.gson.JsonObject> setDeliveryDate(@Header("Authorization") String token, @retrofit2.http.Path("id") int orderId, @Body com.google.gson.JsonObject dateBody);

    @GET("api/my-orders")
    Call<OrderListResponse> getMyOrders(@Header("Authorization") String token);

    @GET("api/orders/{id}")
    Call<OrderDetailResponse> getOrderDetail(@Header("Authorization") String token, @retrofit2.http.Path("id") int orderId);

    // Delivery calculation
    @POST("api/calculate-delivery")
    Call<com.google.gson.JsonObject> calculateDelivery(@Header("Authorization") String token, @Body com.google.gson.JsonObject request);
    
    // Payment verification
    @POST("api/payment/verify")
    @retrofit2.http.FormUrlEncoded
    Call<com.google.gson.JsonObject> verifyEsewaPayment(
        @Header("Authorization") String token,
        @retrofit2.http.Field("order_id") int orderId,
        @retrofit2.http.Field("amount") double amount,
        @retrofit2.http.Field("refId") String transactionUuid
    );

    // User endpoints
    @POST("api/user/default-address")
    Call<com.google.gson.JsonObject> updateDefaultAddress(@Header("Authorization") String token, @Body com.google.gson.JsonObject addressBody);

    @POST("api/profile/update")
    Call<com.google.gson.JsonObject> updateProfile(@Header("Authorization") String token, @Body com.google.gson.JsonObject profileBody);

    @POST("api/account/delete")
    Call<com.google.gson.JsonObject> deleteAccount(@Header("Authorization") String token);

    // Notifications
    @GET("api/notifications")
    Call<com.google.gson.JsonObject> getNotifications(@Header("Authorization") String token);

    @GET("api/notifications/unread-count")
    Call<com.google.gson.JsonObject> getUnreadNotificationCount(@Header("Authorization") String token);

    @POST("api/notifications/{id}/mark-read")
    Call<com.google.gson.JsonObject> markNotificationAsRead(@Header("Authorization") String token, @retrofit2.http.Path("id") int notificationId);

    @POST("api/notifications/mark-all-read")
    Call<com.google.gson.JsonObject> markAllNotificationsAsRead(@Header("Authorization") String token);

    @DELETE("api/notifications/{id}")
    Call<com.google.gson.JsonObject> deleteNotification(@Header("Authorization") String token, @retrofit2.http.Path("id") int notificationId);

    @DELETE("api/notifications")
    Call<com.google.gson.JsonObject> deleteAllNotifications(@Header("Authorization") String token);
}
