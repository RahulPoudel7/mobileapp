package com.example.giftbox.models;

public class NotificationItem {
    private int id;
    private String title;
    private String message;
    private String type; // order, payment, promo, account, delivery
    private boolean isRead;
    private Integer relatedOrderId;
    private String actionUrl;
    private String createdAt;
    private String timeAgo;

    public NotificationItem(int id, String title, String message, String type, boolean isRead,
                           Integer relatedOrderId, String actionUrl, String createdAt, String timeAgo) {
        this.id = id;
        this.title = title;
        this.message = message;
        this.type = type;
        this.isRead = isRead;
        this.relatedOrderId = relatedOrderId;
        this.actionUrl = actionUrl;
        this.createdAt = createdAt;
        this.timeAgo = timeAgo;
    }

    // Getters
    public int getId() { return id; }
    public String getTitle() { return title; }
    public String getMessage() { return message; }
    public String getType() { return type; }
    public boolean isRead() { return isRead; }
    public Integer getRelatedOrderId() { return relatedOrderId; }
    public String getActionUrl() { return actionUrl; }
    public String getCreatedAt() { return createdAt; }
    public String getTimeAgo() { return timeAgo; }

    // Setters
    public void setId(int id) { this.id = id; }
    public void setTitle(String title) { this.title = title; }
    public void setMessage(String message) { this.message = message; }
    public void setType(String type) { this.type = type; }
    public void setRead(boolean read) { isRead = read; }
    public void setRelatedOrderId(Integer relatedOrderId) { this.relatedOrderId = relatedOrderId; }
    public void setActionUrl(String actionUrl) { this.actionUrl = actionUrl; }
    public void setCreatedAt(String createdAt) { this.createdAt = createdAt; }
    public void setTimeAgo(String timeAgo) { this.timeAgo = timeAgo; }
}
