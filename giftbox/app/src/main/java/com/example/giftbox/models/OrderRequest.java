package com.example.giftbox.models;

import com.google.gson.annotations.SerializedName;

public class OrderRequest {
    @SerializedName("gift_id")
    private int giftId;

    @SerializedName("quantity")
    private int quantity;

    @SerializedName("recipient_name")
    private String recipientName;

    @SerializedName("recipient_phone")
    private String recipientPhone;

    @SerializedName("delivery_address")
    private String deliveryAddress;

    @SerializedName("delivery_latitude")
    private double deliveryLatitude;

    @SerializedName("delivery_longitude")
    private double deliveryLongitude;

    @SerializedName("delivery_date")
    private String deliveryDate;

    @SerializedName("payment_method")
    private String paymentMethod;

    @SerializedName("note")
    private String note;

    @SerializedName("gift_wrapping")
    private boolean giftWrapping;

    public OrderRequest(int giftId, int quantity, String recipientName, String recipientPhone,
                       String deliveryAddress, double deliveryLatitude, double deliveryLongitude,
                       String deliveryDate, String paymentMethod, String note, boolean giftWrapping) {
        this.giftId = giftId;
        this.quantity = quantity;
        this.recipientName = recipientName;
        this.recipientPhone = recipientPhone;
        this.deliveryAddress = deliveryAddress;
        this.deliveryLatitude = deliveryLatitude;
        this.deliveryLongitude = deliveryLongitude;
        this.deliveryDate = deliveryDate;
        this.paymentMethod = paymentMethod;
        this.note = note;
        this.giftWrapping = giftWrapping;
    }

    // Getters and Setters
    public int getGiftId() {
        return giftId;
    }

    public void setGiftId(int giftId) {
        this.giftId = giftId;
    }

    public int getQuantity() {
        return quantity;
    }

    public void setQuantity(int quantity) {
        this.quantity = quantity;
    }

    public String getRecipientName() {
        return recipientName;
    }

    public void setRecipientName(String recipientName) {
        this.recipientName = recipientName;
    }

    public String getRecipientPhone() {
        return recipientPhone;
    }

    public void setRecipientPhone(String recipientPhone) {
        this.recipientPhone = recipientPhone;
    }

    public String getDeliveryAddress() {
        return deliveryAddress;
    }

    public void setDeliveryAddress(String deliveryAddress) {
        this.deliveryAddress = deliveryAddress;
    }

    public double getDeliveryLatitude() {
        return deliveryLatitude;
    }

    public void setDeliveryLatitude(double deliveryLatitude) {
        this.deliveryLatitude = deliveryLatitude;
    }

    public double getDeliveryLongitude() {
        return deliveryLongitude;
    }

    public void setDeliveryLongitude(double deliveryLongitude) {
        this.deliveryLongitude = deliveryLongitude;
    }

    public String getDeliveryDate() {
        return deliveryDate;
    }

    public void setDeliveryDate(String deliveryDate) {
        this.deliveryDate = deliveryDate;
    }

    public String getPaymentMethod() {
        return paymentMethod;
    }

    public void setPaymentMethod(String paymentMethod) {
        this.paymentMethod = paymentMethod;
    }

    public String getNote() {
        return note;
    }

    public void setNote(String note) {
        this.note = note;
    }

    public boolean isGiftWrapping() {
        return giftWrapping;
    }

    public void setGiftWrapping(boolean giftWrapping) {
        this.giftWrapping = giftWrapping;
    }
}
