package com.example.giftbox;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.example.giftbox.adapters.NotificationAdapter;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.models.NotificationItem;
import com.example.giftbox.utils.SessionManager;
import com.example.giftbox.view.MyOrdersActivity;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class NotificationActivity extends AppCompatActivity {

    private LinearLayout tabNotifications;
    private LinearLayout tabOrder;
    private LinearLayout tabPromo;
    private ImageView ivTabNotificationIcon, ivTabOrderIcon, ivTabPromoIcon;
    private TextView tvTabNotifications, tvTabOrder, tvTabPromo;
    private RecyclerView rvNotifications;
    private SwipeRefreshLayout swipeRefresh;
    private ProgressBar progressBar;
    private TextView tvEmptyState;
    private TextView tvMarkAllRead;
    private TextView tvClearAll;
    private ImageView ivBack;

    private NotificationAdapter adapter;
    private ApiService apiService;
    private SessionManager sessionManager;
    private int currentTabIndex = 0;
    private String currentFilter = "all"; // all, order, promo

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_notification);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        // Init API and Session
        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);

        initViews();
        setupRecyclerView();
        setupTabClickListeners();
        setupActionButtons();
        selectTab(0);
        
        // Load notifications
        loadNotifications();
    }

    private void initViews() {
        tabNotifications = findViewById(R.id.tabNotifications);
        tabOrder = findViewById(R.id.tabOrder);
        tabPromo = findViewById(R.id.tabPromo);

        ivTabNotificationIcon = findViewById(R.id.ivTabNotificationIcon);
        ivTabOrderIcon = findViewById(R.id.ivTabOrderIcon);
        ivTabPromoIcon = findViewById(R.id.ivTabPromoIcon);

        tvTabNotifications = findViewById(R.id.tvTabNotifications);
        tvTabOrder = findViewById(R.id.tvTabOrder);
        tvTabPromo = findViewById(R.id.tvTabPromo);
        
        rvNotifications = findViewById(R.id.rvNotifications);
        swipeRefresh = findViewById(R.id.swipeRefresh);
        progressBar = findViewById(R.id.progressBar);
        tvEmptyState = findViewById(R.id.tvEmptyState);
        tvMarkAllRead = findViewById(R.id.tvMarkAllRead);
        tvClearAll = findViewById(R.id.tvClearAll);
        ivBack = findViewById(R.id.ivBack);
        
        // Back button
        ivBack.setOnClickListener(v -> finish());
        
        // Swipe to refresh
        swipeRefresh.setOnRefreshListener(this::loadNotifications);
    }

    private void setupRecyclerView() {
        rvNotifications.setLayoutManager(new LinearLayoutManager(this));
        adapter = new NotificationAdapter(this,
                this::handleNotificationClick,
                this::handleNotificationDelete);
        rvNotifications.setAdapter(adapter);
    }

    private void setupActionButtons() {
        tvMarkAllRead.setOnClickListener(v -> markAllAsRead());
        tvClearAll.setOnClickListener(v -> showClearAllDialog());
    }

    private void setupTabClickListeners() {
        tabNotifications.setOnClickListener(v -> {
            selectTab(0);
            currentFilter = "all";
            loadNotifications();
        });

        tabOrder.setOnClickListener(v -> {
            selectTab(1);
            currentFilter = "order";
            loadNotifications();
        });

        tabPromo.setOnClickListener(v -> {
            selectTab(2);
            currentFilter = "promo";
            loadNotifications();
        });
    }

    private void selectTab(int tabIndex) {
        if (currentTabIndex == tabIndex) {
            return;
        }

        resetAllTabs();
        updateActiveTab(tabIndex);
        currentTabIndex = tabIndex;
    }

    private void resetAllTabs() {
        int inactiveIconColor = 0xFF999999;
        int inactiveTextColor = 0xFF777777;

        ivTabNotificationIcon.setColorFilter(inactiveIconColor);
        tvTabNotifications.setTextColor(inactiveTextColor);

        ivTabOrderIcon.setColorFilter(inactiveIconColor);
        tvTabOrder.setTextColor(inactiveTextColor);

        ivTabPromoIcon.setColorFilter(inactiveIconColor);
        tvTabPromo.setTextColor(inactiveTextColor);
    }

    private void updateActiveTab(int tabIndex) {
        int activeIconColor = 0xFFB00020;
        int activeTextColor = 0xFFB00020;

        switch (tabIndex) {
            case 0:
                ivTabNotificationIcon.setColorFilter(activeIconColor);
                tvTabNotifications.setTextColor(activeTextColor);
                break;

            case 1:
                ivTabOrderIcon.setColorFilter(activeIconColor);
                tvTabOrder.setTextColor(activeTextColor);
                break;

            case 2:
                ivTabPromoIcon.setColorFilter(activeIconColor);
                tvTabPromo.setTextColor(activeTextColor);
                break;
        }
    }

    private void loadNotifications() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        if (!swipeRefresh.isRefreshing()) {
            progressBar.setVisibility(View.VISIBLE);
        }
        tvEmptyState.setVisibility(View.GONE);

        Call<JsonObject> call = apiService.getNotifications(token);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                progressBar.setVisibility(View.GONE);
                swipeRefresh.setRefreshing(false);

                if (response.isSuccessful() && response.body() != null) {
                    try {
                        JsonObject responseBody = response.body();
                        JsonArray dataArray = responseBody.getAsJsonArray("data");
                        
                        List<NotificationItem> notifications = new ArrayList<>();
                        for (int i = 0; i < dataArray.size(); i++) {
                            JsonObject notifObj = dataArray.get(i).getAsJsonObject();
                            
                            // Filter by current filter
                            String type = notifObj.get("type").getAsString();
                            if (!currentFilter.equals("all") && !type.equals(currentFilter)) {
                                continue;
                            }
                            
                            NotificationItem item = new NotificationItem(
                                notifObj.get("id").getAsInt(),
                                notifObj.get("title").getAsString(),
                                notifObj.get("message").getAsString(),
                                type,
                                notifObj.get("is_read").getAsBoolean(),
                                notifObj.has("related_order_id") && !notifObj.get("related_order_id").isJsonNull() 
                                    ? notifObj.get("related_order_id").getAsInt() : null,
                                notifObj.has("action_url") && !notifObj.get("action_url").isJsonNull() 
                                    ? notifObj.get("action_url").getAsString() : null,
                                notifObj.get("created_at").getAsString(),
                                notifObj.get("time_ago").getAsString()
                            );
                            notifications.add(item);
                        }
                        
                        adapter.updateNotifications(notifications);
                        
                        if (notifications.isEmpty()) {
                            tvEmptyState.setVisibility(View.VISIBLE);
                            tvEmptyState.setText(getEmptyMessage());
                        }
                    } catch (Exception e) {
                        e.printStackTrace();
                        Toast.makeText(NotificationActivity.this, "Error parsing notifications", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(NotificationActivity.this, "Failed to load notifications", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                progressBar.setVisibility(View.GONE);
                swipeRefresh.setRefreshing(false);
                Toast.makeText(NotificationActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private String getEmptyMessage() {
        switch (currentFilter) {
            case "order":
                return "No order notifications yet";
            case "promo":
                return "No promotional notifications yet";
            default:
                return "No notifications yet";
        }
    }

    private void handleNotificationClick(NotificationItem notification) {
        // Mark as read
        if (!notification.isRead()) {
            markAsRead(notification.getId());
        }
        
        // Navigate based on type/action
        if (notification.getRelatedOrderId() != null) {
            // Navigate to order details
            Intent intent = new Intent(NotificationActivity.this, MyOrdersActivity.class);
            startActivity(intent);
        }
    }

    private void handleNotificationDelete(NotificationItem notification, int position) {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<JsonObject> call = apiService.deleteNotification(token, notification.getId());
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    adapter.removeNotification(position);
                    Toast.makeText(NotificationActivity.this, "Notification deleted", Toast.LENGTH_SHORT).show();
                    
                    // Check if empty
                    if (adapter.getItemCount() == 0) {
                        tvEmptyState.setVisibility(View.VISIBLE);
                        tvEmptyState.setText(getEmptyMessage());
                    }
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                Toast.makeText(NotificationActivity.this, "Failed to delete", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void markAsRead(int notificationId) {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<JsonObject> call = apiService.markNotificationAsRead(token, notificationId);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                // Notification marked as read
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                // Failed to mark as read
            }
        });
    }

    private void markAllAsRead() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<JsonObject> call = apiService.markAllNotificationsAsRead(token);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(NotificationActivity.this, "All marked as read", Toast.LENGTH_SHORT).show();
                    loadNotifications();
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                Toast.makeText(NotificationActivity.this, "Failed to mark all as read", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void showClearAllDialog() {
        new AlertDialog.Builder(this)
                .setTitle("Clear All Notifications")
                .setMessage("Are you sure you want to delete all notifications?")
                .setPositiveButton("Delete All", (dialog, which) -> clearAllNotifications())
                .setNegativeButton("Cancel", null)
                .show();
    }

    private void clearAllNotifications() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<JsonObject> call = apiService.deleteAllNotifications(token);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(NotificationActivity.this, "All notifications deleted", Toast.LENGTH_SHORT).show();
                    loadNotifications();
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                Toast.makeText(NotificationActivity.this, "Failed to delete all", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
