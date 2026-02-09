package com.example.giftbox.manager;

import android.content.Context;
import android.content.SharedPreferences;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.example.giftbox.model.CartItem;
import com.example.giftbox.models.GiftListResponse;
import java.lang.reflect.Type;
import java.util.ArrayList;
import java.util.List;

public class CartManager {
    private static final String PREFS_NAME = "giftbox_cart";
    private static final String CART_KEY = "cart_items";
    private SharedPreferences sharedPreferences;
    private Gson gson;

    public CartManager(Context context) {
        this.sharedPreferences = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        this.gson = new Gson();
    }

    /**
     * Add item to cart or update quantity if exists
     */
    public void addToCart(GiftListResponse.Gift gift, int quantity) {
        List<CartItem> cartItems = getCartItems();

        // Check if item already exists
        CartItem existingItem = cartItems.stream()
                .filter(item -> item.getGiftId() == gift.getId())
                .findFirst()
                .orElse(null);

        if (existingItem != null) {
            // Update quantity
            existingItem.setQuantity(existingItem.getQuantity() + quantity);
        } else {
            // Add new item
            cartItems.add(new CartItem(gift, quantity));
        }

        saveCartItems(cartItems);
    }

    /**
     * Get all cart items
     */
    public List<CartItem> getCartItems() {
        String json = sharedPreferences.getString(CART_KEY, "[]");
        Type type = new TypeToken<List<CartItem>>() {}.getType();
        return gson.fromJson(json, type);
    }

    /**
     * Update quantity of a specific item
     */
    public void updateQuantity(int giftId, int quantity) {
        List<CartItem> cartItems = getCartItems();

        CartItem item = cartItems.stream()
                .filter(ci -> ci.getGiftId() == giftId)
                .findFirst()
                .orElse(null);

        if (item != null) {
            if (quantity <= 0) {
                cartItems.remove(item);
            } else {
                item.setQuantity(quantity);
            }
            saveCartItems(cartItems);
        }
    }

    /**
     * Remove item from cart
     */
    public void removeFromCart(int giftId) {
        List<CartItem> cartItems = getCartItems();
        cartItems.removeIf(item -> item.getGiftId() == giftId);
        saveCartItems(cartItems);
    }

    /**
     * Clear all items from cart
     */
    public void clearCart() {
        sharedPreferences.edit().putString(CART_KEY, "[]").apply();
    }

    /**
     * Get cart item count
     */
    public int getCartCount() {
        return getCartItems().size();
    }

    /**
     * Get total items quantity (sum of all quantities)
     */
    public int getTotalQuantity() {
        return getCartItems().stream()
                .mapToInt(CartItem::getQuantity)
                .sum();
    }

    /**
     * Calculate subtotal
     */
    public double getSubtotal() {
        return getCartItems().stream()
                .mapToDouble(CartItem::getLineTotal)
                .sum();
    }

    /**
     * Check if cart is empty
     */
    public boolean isEmpty() {
        return getCartItems().isEmpty();
    }

    /**
     * Save cart items to SharedPreferences
     */
    private void saveCartItems(List<CartItem> items) {
        String json = gson.toJson(items);
        sharedPreferences.edit().putString(CART_KEY, json).apply();
    }
}
