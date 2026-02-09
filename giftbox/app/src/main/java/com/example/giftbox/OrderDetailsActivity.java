package com.example.giftbox;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.models.OrderDetailResponse;
import com.example.giftbox.utils.SessionManager;
import com.example.giftbox.view.HomeActivity;
import com.google.android.material.button.MaterialButton;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class OrderDetailsActivity extends AppCompatActivity {

    private SessionManager sessionManager;
    private ApiService apiService;
    private int orderId;

    private ImageView backButton;
    private MaterialButton btnBackHome;

    private TextView txtOrderId;
    private TextView txtOrderDate;
    private TextView txtOrderStatus;
    private TextView txtPaymentStatus;
    private TextView txtPaymentMethod;

    private TextView txtItemsList;
    private TextView txtSubtotal;
    private TextView txtDeliveryFee;
    private TextView txtTotalPaid;

    private TextView txtDeliveryName;
    private TextView txtDeliveryAddress;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_order_details);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Initialize API
        sessionManager = new SessionManager(this);
        apiService = ApiClient.getApiService();

        initViews();
        setupClicks();
        
        // Get order ID from intent
        Intent intent = getIntent();
        if (intent != null && intent.hasExtra("order_id")) {
            orderId = intent.getIntExtra("order_id", -1);
            if (orderId != -1) {
                fetchOrderDetails();
            } else {
                Toast.makeText(this, "Invalid order ID", Toast.LENGTH_SHORT).show();
                finish();
            }
        } else {
            Toast.makeText(this, "Order ID not provided", Toast.LENGTH_SHORT).show();
            finish();
        }
    }

    private void initViews() {
        backButton   = findViewById(R.id.backButton);
        btnBackHome  = findViewById(R.id.btnBackHome);

        txtOrderId     = findViewById(R.id.txtOrderId);
        txtOrderDate   = findViewById(R.id.txtOrderDate);
        txtOrderStatus = findViewById(R.id.txtOrderStatus);
        txtPaymentStatus = findViewById(R.id.txtPaymentStatus);
        txtPaymentMethod = findViewById(R.id.txtPaymentMethod);

        txtItemsList   = findViewById(R.id.txtItemsList);
        txtSubtotal    = findViewById(R.id.txtSubtotal);
        txtDeliveryFee = findViewById(R.id.txtDeliveryFee);
        txtTotalPaid   = findViewById(R.id.txtTotalPaid);

        txtDeliveryName    = findViewById(R.id.txtDeliveryName);
        txtDeliveryAddress = findViewById(R.id.txtDeliveryAddress);
    }

    private void fetchOrderDetails() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<OrderDetailResponse> call = apiService.getOrderDetail(token, orderId);
        call.enqueue(new Callback<OrderDetailResponse>() {
            @Override
            public void onResponse(@NonNull Call<OrderDetailResponse> call, @NonNull Response<OrderDetailResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    displayOrderDetails(response.body().getData());
                } else {
                    Toast.makeText(OrderDetailsActivity.this, "Failed to load order details", Toast.LENGTH_SHORT).show();
                    finish();
                }
            }

            @Override
            public void onFailure(@NonNull Call<OrderDetailResponse> call, @NonNull Throwable t) {
                Toast.makeText(OrderDetailsActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
                finish();
            }
        });
    }

    @SuppressLint("SetTextI18n")
    private void displayOrderDetails(OrderDetailResponse.OrderDetail order) {
        // Order Info
        txtOrderId.setText(order.getOrderNumber());
        txtOrderDate.setText("Placed on: " + formatDate(order.getCreatedAt()));
        txtOrderStatus.setText("Status: " + formatStatus(order.getStatus()));
        
        // Payment Status - Check actual payment_status field from database
        String paymentStatus = order.getPaymentStatus();
        boolean isPaid = paymentStatus != null && "paid".equalsIgnoreCase(paymentStatus);
        
        if (isPaid) {
            txtPaymentStatus.setText("Payment: Paid");
            txtPaymentStatus.setTextColor(getResources().getColor(android.R.color.holo_green_dark));
        } else {
            txtPaymentStatus.setText("Payment: Unpaid");
            txtPaymentStatus.setTextColor(getResources().getColor(android.R.color.holo_orange_dark));
        }
        
        // Display payment method
        String paymentMethod = order.getPaymentMethod();
        String methodText = "esewa".equalsIgnoreCase(paymentMethod) ? "eSewa (Online)" : "Cash on Delivery";
        txtPaymentMethod.setText("Method: " + methodText);

        // Items List
        StringBuilder itemsList = new StringBuilder();
        for (int i = 0; i < order.getItems().size(); i++) {
            OrderDetailResponse.OrderDetail.OrderItem item = order.getItems().get(i);
            itemsList.append("• ").append(item.getGift().getName())
                    .append(" x").append(item.getQuantity());
            if (i < order.getItems().size() - 1) {
                itemsList.append("\n");
            }
        }
        txtItemsList.setText(itemsList.toString());

        // Prices
        txtSubtotal.setText("NPR " + formatPrice(order.getSubtotal()));
        
        double totalFees = order.getDeliveryCharge() + order.getPersonalNoteFee() + order.getGiftWrappingFee();
        txtDeliveryFee.setText("NPR " + formatPrice(totalFees));
        
        txtTotalPaid.setText("NPR " + formatPrice(order.getTotalAmount()));

        // Delivery Details
        txtDeliveryName.setText(order.getRecipientName());
        txtDeliveryAddress.setText(order.getDeliveryAddress() + "\n" + order.getRecipientPhone());
    }

    private String formatDate(String dateString) {
        try {
            SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
            SimpleDateFormat outputFormat = new SimpleDateFormat("dd MMM yyyy", Locale.getDefault());
            Date date = inputFormat.parse(dateString);
            return outputFormat.format(date);
        } catch (Exception e) {
            return dateString;
        }
    }

    private String formatStatus(String status) {
        switch (status.toLowerCase()) {
            case "pending": return "Pending";
            case "confirmed": return "Confirmed";
            case "preparing": return "Preparing";
            case "on_the_way": return "On the way";
            case "delivered": return "Delivered";
            case "cancelled": return "Cancelled";
            default: return status;
        }
    }

    private String formatPrice(double price) {
        return String.format(Locale.getDefault(), "%.2f", price);
    }

    private void setupClicks() {
        backButton.setOnClickListener(v -> finish());

        btnBackHome.setOnClickListener(v -> {
            Intent homeIntent = new Intent(OrderDetailsActivity.this, HomeActivity.class);
            homeIntent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
            startActivity(homeIntent);
            finish();
        });
    }
}