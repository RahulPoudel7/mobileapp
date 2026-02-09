package com.example.giftbox.model;

import com.example.giftbox.models.GiftListResponse;

public class CartItem {
    private int id;
    private int giftId;
    private String name;
    private double price;
    private String imageUrl;
    private int quantity;

    // Full constructor
    public CartItem(int id, int giftId, String name, double price, String imageUrl, int quantity) {
        this.id = id;
        this.giftId = giftId;
        this.name = name;
        this.price = price;
        this.imageUrl = imageUrl;
        this.quantity = quantity;
    }

    // Constructor from GiftListResponse.Gift
    public CartItem(GiftListResponse.Gift gift, int quantity) {
        this.giftId = gift.getId();
        this.name = gift.getName();
        this.price = gift.getPrice();
        this.imageUrl = gift.getImage();
        this.quantity = quantity;
    }

    // Getters and Setters
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getGiftId() {
        return giftId;
    }

    public void setGiftId(int giftId) {
        this.giftId = giftId;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public double getPrice() {
        return price;
    }

    public void setPrice(double price) {
        this.price = price;
    }

    public String getImageUrl() {
        return imageUrl;
    }

    public void setImageUrl(String imageUrl) {
        this.imageUrl = imageUrl;
    }

    public int getQuantity() {
        return quantity;
    }

    public void setQuantity(int quantity) {
        this.quantity = quantity;
    }

    public double getLineTotal() {
        return price * quantity;
    }
}
