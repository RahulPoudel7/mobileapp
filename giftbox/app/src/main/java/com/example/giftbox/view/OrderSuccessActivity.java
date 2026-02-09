package com.example.giftbox.view;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.R;
import com.google.android.material.button.MaterialButton;

public class OrderSuccessActivity extends AppCompatActivity {

    private TextView tvOrderNumber, tvOrderAmount, tvSuccessMessage;
    private MaterialButton btnViewOrder, btnContinueShopping;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_order_success);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        initializeViews();
        loadOrderData();
        setupListeners();
    }

    private void initializeViews() {
        tvOrderNumber = findViewById(R.id.tvOrderNumber);
        tvOrderAmount = findViewById(R.id.tvOrderAmount);
        tvSuccessMessage = findViewById(R.id.tvSuccessMessage);
        btnViewOrder = findViewById(R.id.btnViewOrder);
        btnContinueShopping = findViewById(R.id.btnContinueShopping);
    }

    private void loadOrderData() {
        // Get data from intent
        Intent intent = getIntent();
        String orderNumber = intent.getStringExtra("order_number");
        String totalAmount = intent.getStringExtra("total_amount");
        String paymentMethod = intent.getStringExtra("payment_method");
        int orderId = intent.getIntExtra("order_id", 0);

        if (orderNumber != null) {
            tvOrderNumber.setText("Order " + orderNumber);
        }

        if (totalAmount != null) {
            tvOrderAmount.setText("Total: NPR " + totalAmount);
        }

        // Customize message based on payment method
        if ("cod".equals(paymentMethod)) {
            tvSuccessMessage.setText("Thank you for your order!\nYou can pay when you receive your delivery.");
        } else if ("esewa".equals(paymentMethod)) {
            tvSuccessMessage.setText("Payment successful!\nWe'll notify you when your order is on the way.");
        }
    }

    private void setupListeners() {
        btnViewOrder.setOnClickListener(v -> {
            // Navigate to order details
            Intent intent = getIntent();
            int orderId = intent.getIntExtra("order_id", 0);
            
            Intent detailIntent = new Intent(this, com.example.giftbox.OrderDetailsActivity.class);
            detailIntent.putExtra("order_id", orderId);
            startActivity(detailIntent);
            finish();
        });

        btnContinueShopping.setOnClickListener(v -> {
            // Navigate to homepage
            Intent intent = new Intent(this, HomeActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();
        });
    }

    @Override
    public void onBackPressed() {
        // Prevent going back to checkout
        Intent intent = new Intent(this, HomeActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }
}
