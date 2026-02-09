package com.example.giftbox.view;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.util.Log;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.giftbox.AnniversaryCategoryActivity;
import com.example.giftbox.BirthdayCategoryActivity;
import com.example.giftbox.view.CartActivity;
import com.example.giftbox.CorporateCategoryActivity;
import com.example.giftbox.MoreActivity;
import com.example.giftbox.R;
import com.example.giftbox.SeasonalCategoryActivity;
import com.example.giftbox.adapters.GiftAdapter;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.controllers.HomeController;
import com.example.giftbox.manager.CartManager;
import com.example.giftbox.models.GiftListResponse;
import com.example.giftbox.utils.SessionManager;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.button.MaterialButton;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class HomeActivity extends AppCompatActivity {

    private GiftAdapter giftAdapter;
    private RecyclerView rvGifts;
    private ApiService apiService;
    private SessionManager sessionManager;
    private CartManager cartManager;
    private TextView tvSectionTitle;
    private MaterialButton btnBackToFeatured;
    private TextView tvGreeting;

    @SuppressLint("SetTextI18n")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_homepage);

        // Init API and Session Manager
        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);
        cartManager = new CartManager(this);

        // Init controller
        HomeController homeController = new HomeController();

        // Get username from Intent or Session
        final String username = getIntent().getStringExtra("username");
        final String finalUsername = (username == null || username.isEmpty()) ? sessionManager.getUserName() : username;

        // Greeting
        tvGreeting = findViewById(R.id.user_name);
        tvGreeting.setText(homeController.getGreetingText(finalUsername));

        // Profile image click
        ImageView imageViewProfile = findViewById(R.id.imageView);
        imageViewProfile.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, ProfileActivity.class);
            intent.putExtra("username", finalUsername);
            startActivity(intent);
            overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
        });

        // Category cards
        LinearLayout categoryBirthday = findViewById(R.id.category_birthday);
        LinearLayout categoryAnniversary = findViewById(R.id.category_anniversary);
        LinearLayout categoryCorporate = findViewById(R.id.category_corporate);
        LinearLayout categorySeasonal = findViewById(R.id.category_seasonal);

        categoryBirthday.setOnClickListener(v -> {
            fetchGiftsByCategory(1, "Birthday");
        });

        categoryAnniversary.setOnClickListener(v -> {
            fetchGiftsByCategory(2, "Anniversary");
        });

        categoryCorporate.setOnClickListener(v -> {
            fetchGiftsByCategory(4, "Corporate");
        });

        categorySeasonal.setOnClickListener(v -> {
            fetchGiftsByCategory(3, "Seasonal");
        });

        // Setup RecyclerView for gifts
        rvGifts = findViewById(R.id.rv_gifts);
        tvSectionTitle = findViewById(R.id.featured_title);
        btnBackToFeatured = findViewById(R.id.btnBackToFeatured);
        
        // Set up back to featured button
        btnBackToFeatured.setOnClickListener(v -> {
            fetchFeaturedGifts();
        });
        
        rvGifts.setLayoutManager(new GridLayoutManager(this, 2));
        giftAdapter = new GiftAdapter(null, this, gift -> {
            // Handle gift click - navigate to product details
            Toast.makeText(HomeActivity.this, "Clicked: " + gift.getName(), Toast.LENGTH_SHORT).show();
        }, gift -> {
            // Handle add to cart - add to CartManager
            cartManager.addToCart(gift, 1);
            Toast.makeText(HomeActivity.this, gift.getName() + " added to cart!", Toast.LENGTH_SHORT).show();
        });
        rvGifts.setAdapter(giftAdapter);

        // Setup search bar
        EditText searchBar = findViewById(R.id.search_bar);
        searchBar.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                String query = s.toString().trim();
                if (query.isEmpty()) {
                    // If search is empty, fetch featured gifts
                    fetchFeaturedGifts();
                } else {
                    // Search for gifts
                    searchGifts(query);
                }
            }

            @Override
            public void afterTextChanged(Editable s) {}
        });

        // Fetch featured gifts from API
        fetchFeaturedGifts();

        // Bottom navigation
        BottomNavigationView bottomNavigation = findViewById(R.id.bottomNavigation);
        bottomNavigation.setSelectedItemId(R.id.nav_home);

        bottomNavigation.setOnItemSelectedListener(item -> {
            int itemId = item.getItemId();

            if (itemId == R.id.nav_home) {
                return true; // already here
            }

            if (itemId == R.id.nav_cart) {
                Intent intent = new Intent(HomeActivity.this, CartActivity.class);
                intent.putExtra("username", finalUsername);
                startActivity(intent);
                overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
                return true;
            }

            if (itemId == R.id.nav_dot) {
                Intent intent = new Intent(HomeActivity.this, MoreActivity.class);
                intent.putExtra("username", finalUsername);
                startActivity(intent);
                overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
                return true;
            }

            if (itemId == R.id.nav_orders) {
                Intent intent = new Intent(HomeActivity.this, MyOrdersActivity.class);
                intent.putExtra("username", finalUsername);
                startActivity(intent);
                overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
                return true;
            }

            return false;
        });
    }

    private void fetchFeaturedGifts() {
        tvSectionTitle.setText("Featured Gifts");
        btnBackToFeatured.setVisibility(android.view.View.GONE);
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<GiftListResponse> call = apiService.getFeaturedGifts(token);
        call.enqueue(new Callback<GiftListResponse>() {
            @Override
            public void onResponse(@NonNull Call<GiftListResponse> call, @NonNull Response<GiftListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    GiftListResponse giftResponse = response.body();
                    if (giftResponse.getGifts() != null && !giftResponse.getGifts().isEmpty()) {
                        giftAdapter.updateGifts(giftResponse.getGifts());
                    } else {
                        Toast.makeText(HomeActivity.this, "No featured gifts available", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(HomeActivity.this, "Failed to load featured gifts", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<GiftListResponse> call, @NonNull Throwable t) {
                Toast.makeText(HomeActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void fetchGifts() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<GiftListResponse> call = apiService.getGifts(token);
        call.enqueue(new Callback<GiftListResponse>() {
            @Override
            public void onResponse(@NonNull Call<GiftListResponse> call, @NonNull Response<GiftListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    GiftListResponse giftResponse = response.body();
                    if (giftResponse.getGifts() != null && !giftResponse.getGifts().isEmpty()) {
                        // Log images for debugging
                        for (GiftListResponse.Gift gift : giftResponse.getGifts()) {
                            Log.d("HomeActivity", "Gift: " + gift.getName() + ", Image: " + gift.getImage());
                        }
                        giftAdapter.updateGifts(giftResponse.getGifts());
                    } else {
                        Toast.makeText(HomeActivity.this, "No gifts available", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(HomeActivity.this, "Failed to load gifts", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<GiftListResponse> call, @NonNull Throwable t) {
                Toast.makeText(HomeActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void searchGifts(String query) {
        tvSectionTitle.setText("Search Results");
        btnBackToFeatured.setVisibility(android.view.View.VISIBLE);
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<GiftListResponse> call = apiService.searchGifts(token, query);
        call.enqueue(new Callback<GiftListResponse>() {
            @Override
            public void onResponse(@NonNull Call<GiftListResponse> call, @NonNull Response<GiftListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    GiftListResponse giftResponse = response.body();
                    if (giftResponse.getGifts() != null) {
                        if (giftResponse.getGifts().isEmpty()) {
                            Toast.makeText(HomeActivity.this, "No gifts found matching \"" + query + "\"", Toast.LENGTH_SHORT).show();
                        }
                        giftAdapter.updateGifts(giftResponse.getGifts());
                    }
                } else {
                    Toast.makeText(HomeActivity.this, "Search failed", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<GiftListResponse> call, @NonNull Throwable t) {
                Toast.makeText(HomeActivity.this, "Search error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void fetchGiftsByCategory(int categoryId, String categoryName) {
        tvSectionTitle.setText(categoryName + " Gifts");
        btnBackToFeatured.setVisibility(android.view.View.VISIBLE);
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<GiftListResponse> call = apiService.getGiftsByCategory(token, categoryId);
        call.enqueue(new Callback<GiftListResponse>() {
            @Override
            public void onResponse(@NonNull Call<GiftListResponse> call, @NonNull Response<GiftListResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    GiftListResponse giftResponse = response.body();
                    if (giftResponse.getGifts() != null) {
                        if (giftResponse.getGifts().isEmpty()) {
                            Toast.makeText(HomeActivity.this, "No gifts found in " + categoryName, Toast.LENGTH_SHORT).show();
                        } else {
                            Toast.makeText(HomeActivity.this, "Showing " + categoryName + " gifts", Toast.LENGTH_SHORT).show();
                        }
                        giftAdapter.updateGifts(giftResponse.getGifts());
                    }
                } else {
                    Toast.makeText(HomeActivity.this, "Failed to load category gifts", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<GiftListResponse> call, @NonNull Throwable t) {
                Toast.makeText(HomeActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        // Reload greeting with updated username from API
        loadAndUpdateGreeting();
    }

    private void loadAndUpdateGreeting() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<com.google.gson.JsonObject> call = apiService.getProfile(token);
        call.enqueue(new Callback<com.google.gson.JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<com.google.gson.JsonObject> call, @NonNull Response<com.google.gson.JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    try {
                        com.google.gson.JsonObject profileData = response.body();
                        com.google.gson.JsonObject user = profileData.has("user") ? profileData.getAsJsonObject("user") : profileData;
                        String name = user.has("name") ? user.get("name").getAsString() : "User";
                        
                        HomeController homeController = new HomeController();
                        tvGreeting.setText(homeController.getGreetingText(name));
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                }
            }

            @Override
            public void onFailure(@NonNull Call<com.google.gson.JsonObject> call, @NonNull Throwable t) {
                // Silent fail - keep existing greeting
            }
        });
    }

}