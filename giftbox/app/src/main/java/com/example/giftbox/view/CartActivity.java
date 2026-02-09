package com.example.giftbox.view;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.giftbox.R;
import com.example.giftbox.adapter.CartItemAdapter;
import com.example.giftbox.manager.CartManager;
import com.example.giftbox.model.CartItem;
import com.google.android.material.button.MaterialButton;

import java.util.List;

public class CartActivity extends AppCompatActivity implements CartItemAdapter.OnCartItemListener {

    private CartManager cartManager;
    private CartItemAdapter cartItemAdapter;
    private RecyclerView rvCartItems;
    private TextView tvSubtotal, tvTotal;
    private LinearLayout emptyCartContainer;
    private View cartSummaryContainer;
    private MaterialButton btnCheckout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_cart2);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        // Initialize CartManager
        cartManager = new CartManager(this);

        // Initialize views
        initializeViews();

        // Setup RecyclerView
        setupRecyclerView();

        // Load and display cart items
        displayCartItems();

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(android.R.id.content), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
    }

    private void initializeViews() {
        ImageView backButton = findViewById(R.id.backButton);
        rvCartItems = findViewById(R.id.rvCartItems);
        tvSubtotal = findViewById(R.id.subtotalText);
        tvTotal = findViewById(R.id.totalText);
        emptyCartContainer = findViewById(R.id.emptyCartContainer);
        cartSummaryContainer = findViewById(R.id.cartSummaryContainer);
        btnCheckout = findViewById(R.id.btnProceed);

        // Back button
        backButton.setOnClickListener(v -> finish());

        // Checkout button
        btnCheckout.setOnClickListener(v -> proceedToCheckout());
    }

    private void setupRecyclerView() {
        rvCartItems.setLayoutManager(new LinearLayoutManager(this));
        cartItemAdapter = new CartItemAdapter(new java.util.ArrayList<>(), this, this);
        rvCartItems.setAdapter(cartItemAdapter);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void displayCartItems() {
        List<CartItem> cartItems = cartManager.getCartItems();

        if (cartItems.isEmpty()) {
            // Show empty cart message
            emptyCartContainer.setVisibility(View.VISIBLE);
            cartSummaryContainer.setVisibility(View.GONE);
            rvCartItems.setVisibility(View.GONE);
            btnCheckout.setEnabled(false);
        } else {
            // Show cart items
            emptyCartContainer.setVisibility(View.GONE);
            cartSummaryContainer.setVisibility(View.VISIBLE);
            rvCartItems.setVisibility(View.VISIBLE);
            btnCheckout.setEnabled(true);

            // Update adapter
            cartItemAdapter.updateItems(cartItems);
            cartItemAdapter.notifyDataSetChanged();

            // Update summary
            updateOrderSummary();
        }
    }

    @SuppressLint("SetTextI18n")
    private void updateOrderSummary() {
        double subtotal = cartManager.getSubtotal();

        tvSubtotal.setText(String.format("NPR %.0f", subtotal));
        tvTotal.setText(String.format("NPR %.0f", subtotal));
    }

    @Override
    public void onQuantityChanged(CartItem item) {
        if (item.getQuantity() <= 0) {
            cartManager.removeFromCart(item.getGiftId());
        } else {
            cartManager.updateQuantity(item.getGiftId(), item.getQuantity());
        }
        displayCartItems();
    }

    @Override
    public void onItemRemoved(CartItem item) {
        cartManager.removeFromCart(item.getGiftId());
        Toast.makeText(this, item.getName() + " removed from cart", Toast.LENGTH_SHORT).show();
        displayCartItems();
    }

    private void proceedToCheckout() {
        List<CartItem> cartItems = cartManager.getCartItems();

        if (cartItems.isEmpty()) {
            Toast.makeText(this, "Your cart is empty", Toast.LENGTH_SHORT).show();
            return;
        }

        // Pass cart items to CheckoutActivity
        Intent intent = new Intent(CartActivity.this, CheckoutActivity.class);
        startActivity(intent);
    }
}
