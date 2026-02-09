package com.example.giftbox.view;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.view.View;

import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import com.example.giftbox.R;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.utils.SessionManager;
import com.google.gson.JsonObject;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class DeliveryAddressActivity extends AppCompatActivity {
    private ApiService apiService;
    private SessionManager sessionManager;
    private CardView cvDefaultAddress;
    private TextView tvDefaultAddress;
    private LinearLayout emptyState;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_delivery_address);

        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);

        cvDefaultAddress = findViewById(R.id.cvDefaultAddress);
        tvDefaultAddress = findViewById(R.id.tvDefaultAddress);
        emptyState = findViewById(R.id.empty_state);
        ImageView ivBackAddress = findViewById(R.id.ivBackAddress);
        Button btnAddNewAddress = findViewById(R.id.btnAddNewAddress);
        Button btnChangeDefaultAddress = findViewById(R.id.btnChangeDefaultAddress);

        ivBackAddress.setOnClickListener(v -> finish());

        btnAddNewAddress.setOnClickListener(v -> {
            Intent intent = new Intent(DeliveryAddressActivity.this, AddAddressActivity.class);
            intent.putExtra("from", "delivery_address");
            startActivity(intent);
        });

        btnChangeDefaultAddress.setOnClickListener(v -> {
            Intent intent = new Intent(DeliveryAddressActivity.this, AddAddressActivity.class);
            intent.putExtra("from", "change_default_address");
            startActivity(intent);
        });

        loadDefaultAddress();
    }

    private void loadDefaultAddress() {
        String token = sessionManager.getAuthToken();
        if (token == null || token.isEmpty()) {
            return;
        }

        apiService.getProfile("Bearer " + token).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    JsonObject user = response.body().getAsJsonObject("user");
                    String defaultAddress = user.has("default_delivery_address") && !user.get("default_delivery_address").isJsonNull()
                            ? user.get("default_delivery_address").getAsString()
                            : null;

                    if (defaultAddress != null && !defaultAddress.isEmpty()) {
                        tvDefaultAddress.setText(defaultAddress);
                        cvDefaultAddress.setVisibility(View.VISIBLE);
                        emptyState.setVisibility(View.GONE);
                    } else {
                        cvDefaultAddress.setVisibility(View.GONE);
                        emptyState.setVisibility(View.VISIBLE);
                    }
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                cvDefaultAddress.setVisibility(View.GONE);
                emptyState.setVisibility(View.VISIBLE);
            }
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadDefaultAddress();
    }
}
