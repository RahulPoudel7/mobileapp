package com.example.giftbox.models;

import java.io.Serializable;

public class Order implements Serializable {
    private int id;              // Actual order ID from database
    private String orderId;      // Display ID like "ORD-00001"
    private String title;
    private String date;
    private String statusLabel;   // e.g. "Delivered", "On the way"
    private String statusType;    // "active" or "completed"
    private int totalPrice;

    public Order(int id, String orderId, String title, String date,
                 String statusLabel, String statusType, int totalPrice) {
        this.id = id;
        this.orderId = orderId;
        this.title = title;
        this.date = date;
        this.statusLabel = statusLabel;
        this.statusType = statusType;
        this.totalPrice = totalPrice;
    }

    public int getId() { return id; }
    public String getOrderId() { return orderId; }
    public String getTitle() { return title; }
    public String getDate() { return date; }
    public String getStatusLabel() { return statusLabel; }
    public String getStatusType() { return statusType; }
    public int getTotalPrice() { return totalPrice; }
}
