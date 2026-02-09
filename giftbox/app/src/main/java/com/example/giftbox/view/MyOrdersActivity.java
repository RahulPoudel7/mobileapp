package com.example.giftbox.view;

import android.content.Intent;
import android.os.Bundle;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.giftbox.R;
import com.example.giftbox.adapters.MyOrdersAdapter;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.models.Order;
import com.example.giftbox.models.OrderListResponse;
import com.example.giftbox.utils.SessionManager;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class MyOrdersActivity extends AppCompatActivity {

    private MyOrdersAdapter adapter;
    private SessionManager sessionManager;
    private ApiService apiService;

    private TextView chipAll, chipActive, chipCompleted;

    private final List<Order> allOrders = new ArrayList<>();
    private final List<Order> activeOrders = new ArrayList<>();
    private final List<Order> completedOrders = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_my_orders);

        // Initialize SessionManager and ApiService
        sessionManager = new SessionManager(this);
        apiService = ApiClient.getApiService();

        // Back button in header
        ImageView backButton = findViewById(R.id.back_button);
        if (backButton != null) {
            backButton.setOnClickListener(v -> {
                Intent intent = new Intent(MyOrdersActivity.this, HomeActivity.class);
                startActivity(intent);
                finish();
            });
        }

        // Find chips
        chipAll = findViewById(R.id.chip_all);
        chipActive = findViewById(R.id.chip_active);
        chipCompleted = findViewById(R.id.chip_completed);

        // RecyclerView setup
        RecyclerView rvMyOrders = findViewById(R.id.rvMyOrders);
        rvMyOrders.setLayoutManager(new LinearLayoutManager(this));

        // Setup adapter with empty list initially
        adapter = new MyOrdersAdapter(this, new ArrayList<>());
        rvMyOrders.setAdapter(adapter);

        // Chip clicks
        setupChipClicks();
        selectChip("all");

        // Fetch orders from API
        fetchOrders();
    }

    private void fetchOrders() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<OrderListResponse> call = apiService.getMyOrders(token);
        call.enqueue(new Callback<OrderListResponse>() {
            @Override
            public void onResponse(@NonNull Call<OrderListResponse> call, @NonNull Response<OrderListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    OrderListResponse orderResponse = response.body();
                    if (orderResponse.getOrders() != null && !orderResponse.getOrders().isEmpty()) {
                        convertApiOrdersToLocal(orderResponse.getOrders());
                        splitOrdersByStatus();
                        adapter.updateList(new ArrayList<>(allOrders));
                    } else {
                        Toast.makeText(MyOrdersActivity.this, "No orders found", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(MyOrdersActivity.this, "Failed to load orders", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<OrderListResponse> call, @NonNull Throwable t) {
                Toast.makeText(MyOrdersActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void convertApiOrdersToLocal(List<OrderListResponse.OrderItem> apiOrders) {
        allOrders.clear();
        for (OrderListResponse.OrderItem apiOrder : apiOrders) {
            int id = apiOrder.getId();
            String orderId = "ORD-" + String.format("%05d", apiOrder.getId());
            String title = getOrderTitle(apiOrder);
            String date = formatDate(apiOrder.getDeliveryDate());
            String statusLabel = formatStatus(apiOrder.getStatus());
            String statusType = getStatusType(apiOrder.getStatus());
            int totalPrice = (int) apiOrder.getTotalAmount();
            
            allOrders.add(new Order(id, orderId, title, date, statusLabel, statusType, totalPrice));
        }
    }

    private String getOrderTitle(OrderListResponse.OrderItem order) {
        return "Order for " + order.getRecipientName();
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

    private String getStatusType(String status) {
        switch (status.toLowerCase()) {
            case "delivered":
            case "cancelled":
                return "completed";
            default:
                return "active";
        }
    }

    private void splitOrdersByStatus() {
        activeOrders.clear();
        completedOrders.clear();
        for (Order o : allOrders) {
            if ("completed".equals(o.getStatusType())) {
                completedOrders.add(o);
            } else if ("active".equals(o.getStatusType())) {
                activeOrders.add(o);
            }
        }
    }

    private void setupChipClicks() {
        chipAll.setOnClickListener(v -> {
            selectChip("all");
            adapter.updateList(new ArrayList<>(allOrders));
        });

        chipActive.setOnClickListener(v -> {
            selectChip("active");
            adapter.updateList(new ArrayList<>(activeOrders));
        });

        chipCompleted.setOnClickListener(v -> {
            selectChip("completed");
            adapter.updateList(new ArrayList<>(completedOrders));
        });
    }

    private void selectChip(String which) {
        chipAll.setBackgroundResource("all".equals(which) ? R.drawable.chip_selected : R.drawable.chip_unselected);
        chipActive.setBackgroundResource("active".equals(which) ? R.drawable.chip_selected : R.drawable.chip_unselected);
        chipCompleted.setBackgroundResource("completed".equals(which) ? R.drawable.chip_selected : R.drawable.chip_unselected);

        int selectedColor = getResources().getColor(R.color.background);   // white
        int unselectedColor = getResources().getColor(R.color.purple_700); // adjust to your purple

        chipAll.setTextColor("all".equals(which) ? selectedColor : unselectedColor);
        chipActive.setTextColor("active".equals(which) ? selectedColor : unselectedColor);
        chipCompleted.setTextColor("completed".equals(which) ? selectedColor : unselectedColor);
    }
}
