package com.example.giftbox.models;

import com.google.gson.annotations.SerializedName;
import java.util.List;

public class OrderDetailResponse {
    @SerializedName("success")
    private boolean success;

    @SerializedName("data")
    private OrderDetail data;

    public boolean isSuccess() {
        return success;
    }

    public OrderDetail getData() {
        return data;
    }

    public static class OrderDetail {
        @SerializedName("id")
        private int id;

        @SerializedName("order_number")
        private String orderNumber;

        @SerializedName("created_at")
        private String createdAt;

        @SerializedName("status")
        private String status;

        @SerializedName("subtotal")
        private double subtotal;

        @SerializedName("personal_note_fee")
        private double personalNoteFee;

        @SerializedName("gift_wrapping_fee")
        private double giftWrappingFee;

        @SerializedName("delivery_charge")
        private double deliveryCharge;

        @SerializedName("total_amount")
        private double totalAmount;

        @SerializedName("distance_km")
        private double distanceKm;

        @SerializedName("payment_method")
        private String paymentMethod;

        @SerializedName("payment_status")
        private String paymentStatus;

        @SerializedName("recipient_name")
        private String recipientName;

        @SerializedName("recipient_phone")
        private String recipientPhone;

        @SerializedName("delivery_address")
        private String deliveryAddress;

        @SerializedName("has_personal_note")
        private boolean hasPersonalNote;

        @SerializedName("personal_note_text")
        private String personalNoteText;

        @SerializedName("has_gift_wrapping")
        private boolean hasGiftWrapping;

        @SerializedName("items")
        private List<OrderItem> items;

        // Getters
        public int getId() { return id; }
        public String getOrderNumber() { return orderNumber; }
        public String getCreatedAt() { return createdAt; }
        public String getStatus() { return status; }
        public double getSubtotal() { return subtotal; }
        public double getPersonalNoteFee() { return personalNoteFee; }
        public double getGiftWrappingFee() { return giftWrappingFee; }
        public double getDeliveryCharge() { return deliveryCharge; }
        public double getTotalAmount() { return totalAmount; }
        public double getDistanceKm() { return distanceKm; }
        public String getPaymentMethod() { return paymentMethod; }
        public String getPaymentStatus() { return paymentStatus; }
        public String getRecipientName() { return recipientName; }
        public String getRecipientPhone() { return recipientPhone; }
        public String getDeliveryAddress() { return deliveryAddress; }
        public boolean isHasPersonalNote() { return hasPersonalNote; }
        public String getPersonalNoteText() { return personalNoteText; }
        public boolean isHasGiftWrapping() { return hasGiftWrapping; }
        public List<OrderItem> getItems() { return items; }

        public static class OrderItem {
            @SerializedName("quantity")
            private int quantity;

            @SerializedName("price")
            private double price;

            @SerializedName("gift")
            private Gift gift;

            public int getQuantity() { return quantity; }
            public double getPrice() { return price; }
            public Gift getGift() { return gift; }

            public static class Gift {
                @SerializedName("id")
                private int id;

                @SerializedName("name")
                private String name;

                @SerializedName("image_url")
                private String imageUrl;

                public int getId() { return id; }
                public String getName() { return name; }
                public String getImageUrl() { return imageUrl; }
            }
        }
    }
}
