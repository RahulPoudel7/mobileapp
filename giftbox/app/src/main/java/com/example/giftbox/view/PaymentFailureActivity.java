package com.example.giftbox.view;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.OrderDetailsActivity;
import com.example.giftbox.R;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.utils.SessionManager;
import com.google.android.material.button.MaterialButton;
import com.google.gson.JsonObject;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class PaymentFailureActivity extends AppCompatActivity {

    private TextView tvFailureOrderNumber, tvFailureAmount, tvFailureMessage;
    private MaterialButton btnViewFailureOrder, btnContinueShoppingFailure;

    private ApiService apiService;
    private SessionManager sessionManager;

    private String orderNumber;
    private String totalAmount;
    private int orderId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_payment_failure);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);

        initializeViews();
        loadPaymentData();
        setupListeners();
        updateOrderPaymentStatus();
    }

    private void initializeViews() {
        tvFailureOrderNumber = findViewById(R.id.tvFailureOrderNumber);
        tvFailureAmount = findViewById(R.id.tvFailureAmount);
        tvFailureMessage = findViewById(R.id.tvFailureMessage);
        btnViewFailureOrder = findViewById(R.id.btnViewFailureOrder);
        btnContinueShoppingFailure = findViewById(R.id.btnContinueShoppingFailure);
    }

    private void loadPaymentData() {
        Intent intent = getIntent();
        orderNumber = intent.getStringExtra("order_number");
        totalAmount = intent.getStringExtra("total_amount");
        orderId = intent.getIntExtra("order_id", 0);

        if (orderNumber != null) {
            tvFailureOrderNumber.setText("Order " + orderNumber);
        }

        if (totalAmount != null) {
            tvFailureAmount.setText("Amount: NPR " + totalAmount);
        }
    }

    private void setupListeners() {
        btnViewFailureOrder.setOnClickListener(v -> {
            Intent intent = new Intent(this, OrderDetailsActivity.class);
            intent.putExtra("order_id", orderId);
            startActivity(intent);
            finish();
        });

        btnContinueShoppingFailure.setOnClickListener(v -> {
            Intent intent = new Intent(this, HomeActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();
        });
    }

    private void updateOrderPaymentStatus() {
        // Update order status to 'awaiting_payment' in backend
        String token = "Bearer " + sessionManager.getAuthToken();
        
        // Create a request to update payment status
        JsonObject updateRequest = new JsonObject();
        updateRequest.addProperty("status", "awaiting_payment");

        // If there's an update endpoint, use it. For now, we'll just log it
        // In a real scenario, you might want to call an API endpoint like:
        // apiService.updateOrderPaymentStatus(token, orderId, updateRequest)
    }

    @Override
    public void onBackPressed() {
        // Prevent going back
        Intent intent = new Intent(this, HomeActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }
}
